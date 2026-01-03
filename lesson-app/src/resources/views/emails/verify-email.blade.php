<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4F46E5;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TimeBook</h1>
    </div>

    <div class="content">
        <h2>メールアドレスの確認</h2>

        <p>{{ $user->name }} 様</p>

        <p>TimeBookへのご登録ありがとうございます。</p>

        <p>下記のボタンをクリックして、メールアドレスの確認を完了してください。</p>

        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">
                メールアドレスを確認する
            </a>
        </div>

        <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
            ※ このメールに心当たりがない場合は、破棄していただいて構いません。
        </p>

        <p style="margin-top: 20px; font-size: 14px; color: #6b7280;">
            ボタンが機能しない場合は、以下のURLをブラウザにコピー＆ペーストしてください：<br>
            <span style="word-break: break-all;">{{ $verificationUrl }}</span>
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} TimeBook. All rights reserved.</p>
    </div>
</body>
</html>