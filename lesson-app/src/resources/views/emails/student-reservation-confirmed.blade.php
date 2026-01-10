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
            background-color: #54c7f9ff;
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
        .info-box {
            background-color: white;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #54c7f9ff;
        }
        .info-row {
            display: flex;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            color: #6b7280;
        }
        .info-value {
            flex: 1;
            color: #111827;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #EF4444;
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
        <h2>レッスンのご予約確認</h2>

        <p>{{ $reservation->student_name }} 様</p>

        <p>レッスンのご予約を承りました。以下の内容をご確認ください。</p>

        <div class="info-box">
            <div class="info-row">
                <div class="info-label">レッスン日</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($reservation->lessonSlot->date)->format('Y年m月d日') }}（{{ ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($reservation->lessonSlot->date)->dayOfWeek] }}）</div>
            </div>
            <div class="info-row">
                <div class="info-label">レッスン時間</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($reservation->lessonSlot->start_time)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($reservation->lessonSlot->end_time)->format('H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">コース</div>
                <div class="info-value">{{ $reservation->course_type }}分コース</div>
            </div>
            @if($reservation->notes)
            <div class="info-row">
                <div class="info-label">備考</div>
                <div class="info-value">{{ $reservation->notes }}</div>
            </div>
            @endif
        </div>

        <p style="margin-top: 30px;">
            レッスン当日を楽しみにお待ちしております。<br>
            ご不明な点がございましたら、お気軽にお問い合わせください。
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">

        <h3>キャンセルについて</h3>
        <p>やむを得ずキャンセルされる場合は、下記のボタンからお手続きください。</p>

        <div style="text-align: center;">
            <a href="{{ config('app.frontend_url') }}/cancel/{{ $reservation->cancel_token }}" class="button">
                予約をキャンセルする
            </a>
        </div>

        <p style="margin-top: 20px; font-size: 14px; color: #6b7280;">
            ※ キャンセルは、レッスン開始の24時間前までにお願いいたします。
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} TimeBook. All rights reserved.</p>
    </div>
</body>
</html>