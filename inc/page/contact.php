
<div id="contact-text-cont">
<h2><?= CONTACT ?></h2>

<form id="contact-form" method="post">
<input type="hidden" name="csrf-token" value="<?= $csrf ?>">
    <div class="messages"></div>   

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="form_name"><?= YOUR_NAME ?></label>
                    <input id="form_name" type="text" name="name" class="form-control" placeholder="" required="required">
                    <div class="help-block with-errors"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="form_email"><?= YOUR_EMAIL ?></label>
                    <input type="email" id="form_email" name="yourcontact" class="form-control" placeholder="" required="required" >
                    <div class="help-block with-errors"></div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="contact-subject"><?= SUBJECT ?></label>
                    <select class="form-control" id="contact-subject" name="subject">
                        <option value="bug"><?= TECHNICAL_ISSUE ?></option>
                        <option value="advice"><?= ADVICE ?></option>
                        <option value="other"><?= OTHER ?></option>
                    </select>
                </div>
            </div>
       
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="contact-text"><?= MESSAGE ?> <span id="add-fb-profil" style="color:red"></span></label>
                    <textarea  name="message" id="contact-text" class="form-control" placeholder="" rows="4" required="required" ></textarea>
                    <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="col-md-12">
                <input type="hidden" name="is-contact" value="1">
                <div class="g-recaptcha" data-sitekey="6Le5-CMUAAAAAIOtHWSqEIg7KFPQp-VsmQlkN7Ga"></div>
                <br><input type="submit" class="btn btn-primary btn-send" value="<?= SEND_MESSAGE ?>">
            </div>
        </div>
        
    
</form>
</div>