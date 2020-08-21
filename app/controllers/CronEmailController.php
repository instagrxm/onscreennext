<?php
/**
 * Cron Email Controller
 */
class CronEmailController extends Controller
{
    /**
     * Process
     */
    public function process()
    {
        set_time_limit(0);

        $Settings = Controller::model("GeneralData", "settings");
        if ($Settings->get('data.send_expire_email_premium')) {
            $this->sendExpireEmailAccountPremium();
            echo "Cron task processed!";
        } else {
            echo "Cron task no permission!";
        }
    }

    /**
     * Process email sends
     */
    private function sendExpireEmailAccountPremium()
    {        
        $typeEmails = array('expire-premium-3', 'expire-premium-1', 'expire-premium');

        foreach ($typeEmails as $emailType) {      

            $idUser = 0; $numEmails = 0;

            $EmailAutomations = Controller::model('EmailAutomations');
                $EmailAutomations
                    ->where('sent', '>=', 1)
                    ->where('type_email', '=', $emailType)
                    ->setPageSize(5)
                    ->setPage(1)
                    ->orderBy('date', 'ASC')
                    ->fetchData();

            if ($EmailAutomations->getTotalCount() < 1) {
                // There is not any scheduled posts
                //echo "No!<br />";
                //\Slack::sendMessage("RInsta: Nenhum email encontrado! Tipo: ".$emailType, "general");
                continue;
            } 

            foreach ($EmailAutomations->getDataAs("EmailAutomation") as $tE) {           

                $numEmails++;
                if ($numEmails <= count($typeEmails)) {                    
                    // Get accounts
                    if ($idUser != $tE->get('user_id')) {
                        $User = Controller::model("User", $tE->get('user_id'));
                        $idUser = $tE->get('user_id');
                    }

                    if ($User->get('package_id') <= 0) {
                        $tE->delete();
                        continue;
                    }

                    // Duas horas depois
                    $delta = 1 * 1 * 20 *60;
                    $start_time = strtotime($tE->get('date'));
                    $horasDepois = date("Y-m-d H:i:s", $start_time + $delta);

                    // Adicionar um dia a data
                    $delta = 1 * 24 * 60 *60;
                    $umDiaDepois = date("Y-m-d H:i:s", $start_time + $delta);

                    // 3 dias a menos da data de expiração
                    $delta = 3 * 24 * 60 *60;
                    $start_time = strtotime($User->get('expire_date'));
                    $tresDiasMenos = date("Y-m-d H:i:s", $start_time - $delta);

                    // 2 dias a menos da data de expiração
                    $delta = 2 * 24 * 60 *60;
                    $doisDiasMenos = date("Y-m-d H:i:s", $start_time - $delta);

                    // 1 dias a menos da data de expiração
                    $delta = 1 * 24 * 60 *60;
                    $umDiaMenos = date("Y-m-d H:i:s", $start_time - $delta);

                    // 5 horas a menos da data de expiração
                    $delta = 1 * 5 * 60 *60;
                    $cincoHorasMenos = date("Y-m-d H:i:s", $start_time - $delta);

                    $agora = date('Y-m-d H:i:s');                   

                    //echo "IN: ".$dateInterval->days." < ".$tE->get('user_email').'<br />';

                    if ($cincoHorasMenos <= $agora && $User->get('expire_date') >= $agora && 'expire-premium' == $emailType) 
                    {
                        try {
                            // Send notification emails to admins
                            \Email::sendNotification("expire-premium", ["user" => $User]);

                            \Slack::sendMessage(__("Email enviado para user premium: %s | Email: %s | Tipo: %s", $User->get("firstname")." ".$User->get("lastname"), $User->get("email"), $emailType), "payment");
                        } catch (\Exception $e) {
                            // Send notification emails to admins
                        }

                        $tE->delete();
                    }

                    if ('expire-premium' == $emailType) {
                        continue;
                    }

                    if ($tresDiasMenos <= $agora && $User->get('expire_date') >= $agora && 'expire-premium-3' == $emailType)
                    {
                        try {
                            //$U->set('date_email', $eDate)->save();
                            // Send notification emails to admins
                            \Email::sendNotification("expire-premium-3", ["user" => $User]);   

                            \Slack::sendMessage(__("Email enviado para user premium: %s | Email: %s | Tipo: %s", $User->get("firstname")." ".$User->get("lastname"), $User->get("email"), $emailType), "payment");                   

                        } catch (\Exception $e) {
                            // Send notification emails to admins
                        }

                        $tE->delete();

                    }

                    if ('expire-premium-3' == $emailType) {
                        continue;
                    }

                    if ($umDiaMenos <= $agora && $User->get('expire_date') >= $agora && 'expire-premium-1' == $emailType) 
                    {
                        try {
                            // Send notification emails to admins
                            \Email::sendNotification("expire-premium-1", ["user" => $User]);

                            \Slack::sendMessage(__("Email enviado para user premium: %s | Email: %s | Tipo: %s", $User->get("firstname")." ".$User->get("lastname"), $User->get("email"), $emailType), "payment");
                        } catch (\Exception $e) {
                            // Send notification emails to admins
                        }

                        $tE->delete();

                    }
                    
                } else {
                    break;
                }
            }
        }
        return true;      
    }
}