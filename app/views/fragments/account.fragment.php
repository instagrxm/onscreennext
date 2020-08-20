        <div class='skeleton' id="account">
            <form class="js-ajax-form" 
                  action="<?= APPURL . "/accounts/" . ($Account->isAvailable() ? $Account->get("id") : "new") ?>"
                  method="POST"
                  autocomplete="off">
                <input type="hidden" name="action" value="save">

                <div class="container-1200">
                    <div class="row clearfix">
                        <div class="col s12 m8 l4">
                            <section class="section">
                                <div class="section-content">
                                    <div class="form-result">
                                    </div>

                                    <div class="js-login">
                                        <div class="mb-20">
                                            <label class="form-label">
                                                <?= __("Username") ?>
                                                <span class="compulsory-field-indicator">*</span>    
                                            </label>

                                            <input class="input js-required"
                                                   name="username" 
                                                   type="text" 
                                                   value="<?= htmlchars($Account->get("username")) ?>" 
                                                   placeholder="<?= __("Enter username") ?>"
                                                   maxlength="30">
                                        </div>

                                        <div class="mb-20">
                                        <label class="form-label">
                                            <?= __("Password") ?>
                                                <span class="compulsory-field-indicator">*</span>    
                                        </label>

                                        <input class="input js-required"
                                            name="password" 
                                            type="password" 
                                            placeholder="<?= __("Enter password") ?>">
                                        </div>

                                        <div class="mb-20">
                                        <label class="form-label">
                                            <?= __("Verification") ?> 
                                        </label>

                                        <select class="input" name="choice">
                                        <option value="1"><?= __("E-mail") ?></option>
                                        <option value="2"><?= __("Mobile phone") ?></option> 
                                        <option value="3"><?= __("Browser Extension") ?></option>
                                        </select> 

                                            <ul class="field-tips">
                                                <li><?= __("Sometimes Instagram can ask you to verify your identity, choose preferred verification method.") ?></li>
                                                <li><?= __("Email method is used by default.") ?></li>
                                            </ul>
                                        </div>

                                        <?php if ($Settings->get("data.proxy") && $Settings->get("data.user_proxy")): ?>
                                            <div class="mt-20">
                                                <label class="form-label"><?= __("Proxy") ?> (<?= ("Optional") ?>)</label>

                                                <input class="input"
                                                       name="proxy" 
                                                       type="text" 
                                                       value="<?= htmlchars($Account->get("proxy")) ?>" 
                                                       placeholder="<?= __("Proxy for your country") ?>">
                                            </div>

                                            <ul class="field-tips">
                                                <li><?= __("Proxy should match following pattern: http://ip:port OR http://username:password@ip:port") ?></li>
                                                <li><?= __("It's recommended to to use a proxy belongs to the country where you've logged in this acount in Instagram's official app or website.") ?></li>
                                            </ul>
                                        <?php endif ?>
                                    </div>

                                    <div class="js-browser-extension none">
                                    <input type="hidden" name="iacid" value="" disabled>
                                    <div class="mb-20">
                                        <ul class="field-tips-ext">
                                            <li class="mb-5"><?= __("Install <a href='%s' target='_blank'>Cookie Downloader</a> extension for Google Chrome, Opera and Firefox.", "https://chrome.google.com/webstore/detail/cookie-downloader/epldkbdhmpdcdgcolndopgkigelnmlmo") ?></li>
                                            <li class="mb-5"><?= __("Go to <a href='https://www.instagram.com/accounts/login/' target='_blank'>Instagram</a> website and login to your account.") ?></li>
                                            <li class="mb-5"><?= __("Download cookie file by clicking <b>Download</b> button in extension.") ?></li>
                                            <li class="mb-5"><?= __("Attach cookie to the form below.") ?></li>
                                        </ul>
                                    </div>
                                    <div class="mb-20 cookie-file-extension" style="display: none;">
                                        <label class="form-label"><?= __("Cookie file") ?></label>
                                        <div class="pos-r">
                                            <input class="input inputfile-session rightpad"
                                                name="cookie-file-extension"
                                                type="file" 
                                                id="cookie-file-extension"
                                                value="">
                                            <label class="input cookie-file-label" for="cookie-file-extension">
                                                <span><?= __("Choose file username-cookie.dat") ?></span>
                                                <span class="mdi mdi-folder field-icon--right"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="js-2fa none">
                                        <input type="hidden" name="2faid" value="" disabled>

                                        <div class="mb-20">
                                            <label class="form-label">
                                                <?= __("Security Code") ?>
                                                <span class="compulsory-field-indicator">*</span>    
                                            </label>

                                            <input class="input js-required"
                                                   name="twofa-security-code"
                                                   type="text" 
                                                   value="" 
                                                   placeholder="<?= __("Enter security code") ?>"
                                                   maxlength="8"
                                                   disabled>
                                        </div>

                                        <div>
                                            <div class="js-not-received-security-code">
                                                <?= __("Didn't get a security code?") ?>
                                                <a class="resend-btn" href='javascript:void(0)'>
                                                    <?= __("Resend it") ?>
                                                    <span class="timer" data-text="<?= __("after %s seconds", "{seconds}") ?>"></span>
                                                </a>
                                            </div>
                                            <div class="resend-result color-danger fz-12"></div>
                                        </div>

                                        <p class="backup-note">
                                            <?= 
                                                __(
                                                    "If you're unable to receive a security code, use one of your <a href='{url}' target='_blank'>backup codes</a>.", 
                                                    ["{url}" => "https://help.instagram.com/1006568999411025"]
                                                );
                                            ?>
                                        </p>
                                    </div>

                                    <div class="js-challenge none">
                                        <input type="hidden" name="challengeid" value="" disabled>

                                        <div class="mb-20">
                                            <label class="form-label">
                                                <?= __("Security Code") ?>
                                                <span class="compulsory-field-indicator">*</span>    
                                            </label>

                                            <input class="input js-required"
                                                   name="challenge-security-code"
                                                   type="text" 
                                                   value="" 
                                                   placeholder="<?= __("Enter security code") ?>"
                                                   maxlength="6"
                                                   disabled>
                                        </div>

                                        <div>
                                            <div class="js-not-received-security-code">
                                                <?= __("Didn't get a security code?") ?>
                                                <a class="resend-btn" href='javascript:void(0)'>
                                                    <?= __("Resend it") ?>
                                                    <span class="timer" data-text="<?= __("after %s seconds", "{seconds}") ?>"></span>
                                                </a>
                                            </div>
                                            <div class="resend-result color-danger fz-12"></div>
                                        </div>

                                        <p class="backup-note">
                                            <?= 
                                                __("You should receive the 6 digit security code sent by Instagram.");
                                            ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="js-login">
                                    <input class="fluid button button--footer js-login" type="submit" value="<?= $Account->isAvailable() ? __("Save changes") :  __("Add account") ?>">
                                </div>

                                <div class="js-2fa js-challenge js-browser-extension js-challenge-phone none">
                                    <input class="fluid button button--footer" type="submit" value="<?= __("Confirm") ?>">
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        