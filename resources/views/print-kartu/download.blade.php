<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kartu {{ $record->jenis_kartu }} #{{ $record->id }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
        }
        h1 { font-size: 20px; margin-bottom: 8px; }
        p { color: #6b7280; margin-bottom: 8px; }
    </style>
</head>
<body>
    <h1>Kartu {{ $record->jenis_kartu }} #{{ $record->id }}</h1>
    <p>Jumlah kartu: {{ $record->jumlah ?? 1 }}</p>
    <p>Template: {{ $record->template->nama_batch ?? '-' }}</p>
    <p>Dibuat: {{ $record->created_at->format('d M Y H:i') }}</p>
</body>
</html>