<?php

namespace App\StoreProject\Components\GoogleSheet\Enums\Wildberries;

Enum ProductListEnum : int
{
    case barcode = 0;
    case article = 1;
    case name = 2;
    case sizeA = 3; //XL
    case sizeB = 4; //46
    case color = 5;
    case photo_link = 6;
    case photo = 7;
    case barcode_print_link = 8;
}
