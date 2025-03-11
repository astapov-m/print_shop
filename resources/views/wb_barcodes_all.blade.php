<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Печать заказа {{$filteredRows[7]}}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 3.5mm;
        }
    </style>
</head>
<body>
<div style="width: 100mm; height: 53mm; position: relative; margin-left: -8mm;" >
    <table width="100%" style="position: relative; margin-top: -10mm; margin-left: -2mm">
        <tr>
            <td style="">@php echo $filteredRows[6]; @endphp</td>
        </tr>
    </table>
    <p style="position: relative; margin-top: -1.8mm; margin-left: 30mm;">{{$filteredRows[0]}}</p>
    <p style="position: relative; margin-top: -1.8mm; margin-left: 30mm; font-weight: bold; font-size: 3.2mm;">BLOOM CENTURY</p>
    <p style="position: relative; margin-top: -3.8mm;">{{$filteredRows[2]}}</p>
    <p style="position: relative; margin-top: -4.5mm;">Цвет: {{$filteredRows[5]}} / Размер: {{$filteredRows[3]}}</p>
</div>
<div style="width: 100mm; height: 53mm; position: relative; margin-left: -8mm;" >
    <table width="100%" style="position: relative; margin-top: -10mm; margin-left: -2mm">
        <tr>
            <td style="">@php echo $filteredRows[6]; @endphp</td>
        </tr>
    </table>
    <p style="position: relative; margin-top: -1.8mm; margin-left: 30mm;">{{$filteredRows[0]}}</p>
    <p style="position: relative; margin-top: -1.8mm; margin-left: 30mm; font-weight: bold; font-size: 3.2mm;">BLOOM CENTURY</p>
    <p style="position: relative; margin-top: -3.8mm;">{{$filteredRows[2]}}</p>
    <p style="position: relative; margin-top: -4.5mm;">Цвет: {{$filteredRows[5]}} / Размер: {{$filteredRows[3]}}</p>
</div>
<div>
    <img style="position: relative; margin-top: -14mm; margin-left: -11mm;" src="storage/wb/barcodes/{{$filteredRows[7]}}.png" width="380px" height="285px" alt="Описание изображения">
</div>
@if(!is_null($filteredRows[8]) && $filteredRows[8] != '-')
    <div>
        <img style="position: relative; margin-top: -8mm; margin-left: -7.5mm;" src="storage/wb/kiz/{{$filteredRows[7]}}.png" width="138mm" height="138mm" alt="Описание изображения">
        <p style="position: relative; margin-top: -20mm; margin-left: 40mm; font-weight: bold; font-size: 3.2mm;">{{$filteredRows[10]}} {{substr_replace($filteredRows[5], 'ая', -1)}}, с рисунком, размер {{$filteredRows[3]}}</p>
        <img style="position: relative; margin-top: -38mm; margin-left: 57.5mm;" src="storage/images.png" width="100mm" height="45mm" alt="Описание изображения">
        <p style="position: relative; margin-top: -4.3mm; font-weight: bold; font-size: 3.1mm; margin-left: -8mm">(01){{$filteredRows[8]}}</p>
        <p style="position: relative; font-weight: bold; font-size: 3.1mm; margin-left: -8mm; margin-top: -1.4mm; ">(21){{$filteredRows[9]}}</p>
    </div>
    <div>
        <img style="position: relative; margin-top: -8mm; margin-left: -7.5mm;" src="storage/wb/kiz/{{$filteredRows[7]}}.png" width="138mm" height="138mm" alt="Описание изображения">
        <p style="position: relative; margin-top: -20mm; margin-left: 40mm; font-weight: bold; font-size: 3.2mm;">{{$filteredRows[10]}} {{substr_replace($filteredRows[5], 'ая', -1)}}, с рисунком, размер {{$filteredRows[3]}}</p>
        <img style="position: relative; margin-top: -38mm; margin-left: 57.5mm;" src="storage/images.png" width="100mm" height="45mm" alt="Описание изображения">
        <p style="position: relative; margin-top: -4.3mm; font-weight: bold; font-size: 3.1mm; margin-left: -8mm">(01){{$filteredRows[8]}}</p>
        <p style="position: relative; font-weight: bold; font-size: 3.1mm; margin-left: -8mm; margin-top: -1.4mm; ">(21){{$filteredRows[9]}}</p>
    </div>
@endif
</body>
</html>
