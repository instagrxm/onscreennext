            <form class="js-ajax-form" 
                  action="<?= APPURL . "/settings/" . $page ?>"
                  method="POST">
                <input type="hidden" name="action" value="save">

                <div class="section-header clearfix">
                    <h2 class="section-title"><?= __("Send Emails") ?></h2>
                    <div class="section-actions clearfix hide-on-large-only">
                        <a class="mdi mdi-menu-down icon js-settings-menu" href="javascript:void(0)"></a>
                    </div>
                </div>

                <div class="section-content">
                    <div class="clearfix">
                        <div class="col s12 m6 l5">
                            <div class="form-result"></div>

                            <div class="mb-40 s12 m6 l6">
                                <label>
                                    <input type="checkbox" 
                                           class="checkbox" 
                                           name="send_expire_email_free" 
                                           value="1" 
                                           <?= $Settings->get("data.send_expire_email_free") ? "checked" : "" ?>>
                                    <span>
                                        <span class="icon unchecked">
                                            <span class="mdi mdi-check"></span>
                                        </span>
                                        <?= __('Send Email Expire Free') ?>
                                    </span>
                                </label>
                                <ul class="field-tips">
                                    <li><?= __("Send emails to free accounts that expire.") ?></li>
                                </ul>
                            </div>

                            <div class="mb-40 s12 m6 l6">
                                <label>
                                    <input type="checkbox" 
                                           class="checkbox" 
                                           name="send_expire_email_premium" 
                                           value="1" 
                                           <?= $Settings->get("data.send_expire_email_premium") ? "checked" : "" ?>>
                                    <span>
                                        <span class="icon unchecked">
                                            <span class="mdi mdi-check"></span>
                                        </span>
                                        <?= __('Send Email Expire Premium') ?>
                                    </span>
                                </label>
                                <ul class="field-tips">
                                    <li><?= __("Send 3 emails to paid accounts(non recurrent) that will expire.") ?></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col s12 m6 m-last l6 l-last offset-l1 mb-40 js-notes">
                            <label class="form-label mb-10"><?= __("Notes") ?></label>

                            <p>
                                <?= __("Adicione uma nova cron com o formato abaixo:") ?>
                            </p>

                            <ul class="field-tips">
                                <li class="mb-15">
                                    <?= __("*/5 * * * * wget --quiet -O /dev/null %s/%s", APPURL, "cronemailalexandre") ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="clearfix">
                      <div class="col s12 m6 l5">
                          <input class="fluid button button--oval" type="submit" value="<?= __("Save") ?>">
                      </div>
                    </div>
                </div>
            </form>