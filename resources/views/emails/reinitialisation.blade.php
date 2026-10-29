<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;background:#f1f5f3;font-family:Arial,Helvetica,sans-serif;color:#0d2b24">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr><td align="center" style="padding:24px 12px">
        <table width="560" cellpadding="0" cellspacing="0" role="presentation" style="max-width:560px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden">
            <tr><td style="background:#0d2b24;padding:20px 28px;color:#ffffff;font-size:22px;font-weight:bold">{{ config('app.name') }}</td></tr>
            <tr><td style="padding:28px;font-size:16px;line-height:1.6">
                <p style="margin:0 0 14px">Bonjour {{ strtok($utilisateur->name, ' ') }},</p>
                <p style="margin:0 0 14px">Tu as demandé à réinitialiser ton mot de passe. Clique sur le bouton ci-dessous pour en choisir un nouveau. Ce lien est valable <strong>{{ $minutes }} minutes</strong>.</p>
                <p style="text-align:center;margin:28px 0">
                    <a href="{{ $lien }}" style="background:#0f7a4f;color:#ffffff;text-decoration:none;padding:14px 26px;border-radius:10px;font-weight:bold;display:inline-block">Choisir un nouveau mot de passe</a>
                </p>
                <p style="margin:0 0 14px;font-size:14px;color:#566d66">Si le bouton ne marche pas, copie cette adresse dans ton navigateur :<br>
                    <a href="{{ $lien }}" style="color:#0f7a4f;word-break:break-all">{{ $lien }}</a></p>
                <p style="margin:0;font-size:14px;color:#566d66">Tu n'as rien demandé ? Ignore cet e-mail : ton mot de passe actuel reste valable.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>