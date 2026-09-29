<label>
    Értesítendő email címek <span class="muted">(vesszővel elválasztva, max 255 karakter – ők automatikusan hozzáférést is kapnak)</span>
    <input type="text" name="notification_emails" maxlength="255"
           value="{{ old('notification_emails', $user->notification_emails) }}">
</label>
<label>
    Értesítendő telefonszámok <span class="muted">(vesszővel elválasztva, max 255 karakter)</span>
    <input type="text" name="notification_phones" maxlength="255"
           value="{{ old('notification_phones', $user->notification_phones) }}">
</label>
<label>
    Hozzáférés az eseményeimhez <span class="muted">(Google-fiók email címek, soronként egy – belépés után látják az eseménylistádat)</span>
    <textarea name="allowed_emails" rows="6">{{ old('allowed_emails', $allowedEmails) }}</textarea>
</label>
