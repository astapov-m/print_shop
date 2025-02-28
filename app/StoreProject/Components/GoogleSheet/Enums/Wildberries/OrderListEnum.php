<?php

namespace App\StoreProject\Components\GoogleSheet\Enums\Wildberries;

enum OrderListEnum : int
{
    case order_id = 0;
    case barcode = 1;
    case label_number = 2;
    case article = 3;
    case name = 4;
    case photo = 5;
    case supply = 6;
    case print_link = 7;
    case kizA = 8;
    case kizB = 9;
}
