<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Баркод</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 3mm;
        }
    </style>
</head>
<body>
<div>
    <table width="100%" style="position: relative; margin-top: -10mm;">
        <tr>
            <td style="padding-left: 14mm;">@php echo $filteredRows[6]; @endphp</td>
        </tr>
    </table>
    <p style="position: relative; margin-top: -1.8mm; margin-left: 26.5mm">{{$filteredRows[0]}}</p>
    <p style="position: relative; margin-top: -1.8mm; margin-left: 25mm; font-weight: bold; font-size: 3.2mm">BLOOM CENTER</p>
    <p>{{$filteredRows[2]}}</p>
    <p>Артикул: {{$filteredRows[1]}}</p>
    <p>Цвет: {{$filteredRows[0]}} / Размер: {{$filteredRows[3]}}</p>

</div>
</body>
</html>
