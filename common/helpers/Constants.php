<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 02/02/2015
 * Time: 12:33 AM
 */

namespace common\helpers;


class Constants {
    const ROWS_PER_PAGE = 20;

    //filter
    const FILTER_ALL = '0';
    const FILTER_FEATURES = '1';
    const FILTER_HOT = '2';

    const BUY_ONE = 0;
    const BUY_PACKAGE = 1;
    const RENEW = 2;

    const ACTIVE = 10;
    const INACTIVE = 0;

    const OBTAINE_FREE = 1;
    const OBTAINE_PURCHASE= 2;
    const OBTAINE_PURCHASE_PACKAGE = 3;
    const OBTAINE_RECEIVE = 4;
}