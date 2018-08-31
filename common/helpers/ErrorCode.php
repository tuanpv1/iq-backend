<?php
/**
 * Created by PhpStorm.
 * User: HungChelsea
 * Date: 01-Mar-17
 * Time: 1:44 PM
 */

namespace common\helpers;


class ErrorCode
{
    const NUMBER_PROMOTION_NOT_ENOUGH = 'Số lượt khuyến mãi vượt quá giới hạn';
    const SUBSCRIBER_NOT_BELONG_TO_CAMPAIGN = 'Người dùng không thuộc nhóm hưởng chiến dịch';
    const SERVICE_NOT_BELONG_TO_CAMPAIGN = 'Gói cước không thuộc chiến dịch';
    const NOT_FOUND_SUBSCRIBER = 'Khách hàng không tồn tại';
    const NOT_FOUND_SERVICE = 'Gói cước không tồn tại không tồn tại';
    const CAMPAIGN_SUCCESS = 'Áp dụng khuyến mãi thành công';
    const CAMPAIGN_ERROR = 'Áp dụng khuyễn mãi không thành công';
    const NOT_FOUND_CAMPAIGN = 'Không tồn tại chiến dịch';
    const NOT_CONDITION_CAMPAIGN = 'Không thoải mãn điều kiện';
}