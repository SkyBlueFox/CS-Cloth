<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0;padding:24px;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:24px;padding:32px;">
        <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#2563eb;">
            CS Cloth
        </p>
        <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">{{ $headline }}</h1>
        <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#475569;">
            {{ $instruction }}
        </p>
        <div style="margin:0 0 24px;padding:20px 24px;border-radius:20px;background:#eff6ff;border:1px solid #bfdbfe;text-align:center;">
            <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#2563eb;">
                One-Time Password
            </p>
            <p style="margin:0;font-size:34px;font-weight:800;letter-spacing:0.35em;color:#0f172a;">
                {{ $otpCode }}
            </p>
        </div>
        <p style="margin:0;font-size:14px;line-height:1.6;color:#64748b;">
            This code expires in {{ $expiresInMinutes }} minutes. If you did not request this, you can ignore this email.
        </p>
    </div>
</body>
</html>
