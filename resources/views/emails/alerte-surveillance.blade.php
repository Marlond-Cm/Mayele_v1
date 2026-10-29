<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="margin:0;background:#f1f5f3;font-family:Arial,Helvetica,sans-serif;color:#0d2b24">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr><td align="center" style="padding:24px 12px">
        <table width="580" cellpadding="0" cellspacing="0" role="presentation" style="max-width:580px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden">
            <tr><td style="background:#d93a2b;padding:18px 28px;color:#ffffff;font-size:20px;font-weight:bold">⚠ Alerte paiements</td></tr>
            <tr><td style="padding:28px;font-size:16px;line-height:1.6">
                <p style="margin:0 0 14px">Un problème a été détecté sur les paiements de {{ config('app.name') }} :</p>
                <ul style="margin:0 0 18px;padding-left:20px">
                    @foreach ($problemes as $message)
                        <li style="margin-bottom:10px">{{ $message }}</li>
                    @endforeach
                </ul>
                <p style="text-align:center;margin:24px 0">
                    <a href="{{ route('admin.surveillance') }}" style="background:#0f7a4f;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-weight:bold;display:inline-block">Voir la surveillance</a>
                </p>
                <p style="margin:0;font-size:13px;color:#566d66">Cette alerte n'est envoyée qu'une fois par heure et par type de problème.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>