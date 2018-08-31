<?php
/**
 * Created by PhpStorm.
 * User: VS9 X64Bit
 * Date: 21/05/2015
 * Time: 11:25 AM
 */
namespace api\helpers;


use Yii;

class Message
{
//    const MSG_SUCCESS = 'Thành công.';
//    const MSG_FAIL = 'Không thành công. Vui lòng thử lại';
//    const MSG_FAIL_WHEN_CHANGE_STATUS_CONTENT = 'Lỗi hệ thống, không thể cập nhật trạng thái của nội dung phân phối. Vui lòng thử lại';
//    const MSG_NOT_DATA = 'Không có dữ liệu';
//    const MSG_ERROR_SYSTEM = 'Hệ thống hiện không thể thực hiện chức năng này, xin vui lòng quay lại sau';
//    const MSG_LOGIN_FAIL_PASSWORD_NOT_CORRECT = 'Tên tài khoản hoặc mật khẩu chưa đúng. Vui lòng thử lại';
//    const MSG_LOGIN_FAIL_USER_INACTIVE = 'Tài khoản đang tạm dừng, vui lòng liên hệ với quản trị viên để được trợ giúp';
//    const MSG_ADD_SUCCESS = 'Thêm mới thành công';
//    const MSG_UPDATE_SUCCESS = 'Cập nhật thành công';
//    const MSG_DELETE_SUCCESS = 'Xóa thành công';
//    const MSG_LOGIN_SUCCESS = 'Quý khách đã đăng nhập thành công!';
//    const MSG_LOGIN_SUBSCRIBER_INACTIVE = 'Tài khoản đang không thể sử dụng. Quý khách vui lòng liên hệ tổng đài CSKH 1900xxxx để có thêm thông tin chi tiết. Xin cám ơn!';
////    const MSG_REGISTER_SUCCESS = 'Quý khách đã đăng ký thành công tài khoản thuê bao. Vui lòng đăng nhập để sử dụng các chức năng của ứng dụng';
//    const MSG_REGISTER_SUCCESS = 'Quý khách đã đăng ký thành công tài khoản. Quý khách đã có thể sử dụng dịch vụ ngay từ lúc này';
//    const MSG_VERIFY_TOKEN_WRONG = 'Mã xác thực không chính xác, xin vui lòng kiểm tra lại';
//    const MSG_VERIFY_TRUE = 'Chúc mừng bạn đã đăng ký thành công tài khoản. Hệ thống sẽ tự động chuyển bạn về trang trước.';
////    const MSG_NULL_VALUE = 'Không được để rỗng  ';
//    const MSG_NULL_VALUE = 'Trường {1} bắt buộc nhập!';
//    const MSG_NOT_EMPTY = '{1} không được phép để trống';
//    const MSG_NOT_FOUND_USER = 'Người dùng không tồn tại';
//    const MSG_WRONG_USERNAME_OR_PASSWORD = 'Thông tin tài khoản hoặc mật khẩu không hợp lệ';
//    const MSG_CHANGE_PASSWORD_SUCCESS = 'Đổi mật khẩu thành công';
//    const MSG_OLD_PASSWORD_WRONG = 'Mật khẩu cũ không đúng, Quý khách vui lòng nhập lại';
//    const MSG_UPDATE_PROFILE = 'Cập nhật thành công';
//    const WRONG_PHONE_NUMBER_REGISTER = 'Số điện thoại đăng ký không hợp lệ, vui lòng kiểm tra lại.';
//    const WRONG_PHONE_NUMBER_LOGIN = 'Số điện thoại đăng nhập không hợp lệ, vui lòng kiểm tra lại.';
//    const CONTENT_PROFILE_NOT_FOUND = 'Content Profile không tồn tại';
//    const MSG_DEFAULT_CONTENT_PROFILE_NOT_FOUND = 'Nội dung không tồn tại Content profile ở thị trường default ';
//    const CONTENT_PROFILE_UPDATE_CONVERTED_SUCCESS = 'Cập nhật content profile thành công';
//    const MSG_NOT_FOUND_CONTENT_PROFILE = 'Không tìm thấy chất lượng của nội dung này.';
//    const MSG_NOT_FOUND_CONTENT = 'Không tìm thấy nội dung.';
//    const MSG_NOT_FOUND_SERVICE = 'Không tìm thấy gói cước.';
//    const MSG_NOT_FOUND_STREAMING = 'Không tìm thấy streaming.';
//    const MSG_SYNC_DATA_TO_SITE_SUCCESS = 'Hệ thống bắt đầu phân phối nội dung đây là quá trình bất đồng bộ. Vui lòng kiểm tra lại sau';
//    const MSG_SUBSCRIBER_NOT_FOUND = 'Số điện thoại chưa đăng ký sử dụng dịch vụ';
//    const MSG_ACTION_FAIL = 'Không thành công. Quý khách vui lòng thử lại sau ít phút.';
//    const MSG_ACTION_FAVORITE_SUCCESS = 'Quý khách đã cập nhật danh sách yêu thích thành công.';
//    const MSG_ACTION_FAVORITE_FALSE = 'Quý khách đã cập nhật danh sách yêu thích thất bại.';
//    const MSG_ACTION_UNFAVORITE_SUCCESS = 'Quý khách đã xóa thành công nội dung khỏi danh sách yêu thích.';
//    const MSG_ACTION_FAVORITE_ALREADY = 'Nội dung này đã nằm trong danh sách yêu thích của quý khách.';
//    const MSG_ACTION_UNFAVORITE_ALREADY = 'Nội dung này không nằm trong danh sách yêu thích của quý khách.';
//    const MSG_ACTION_COMMENT_NO_CONTENT = 'Không thành công. Quý khách vui lòng nhập lời bình.';
//    const MSG_ACTION_FEEDBACK_SUCCESS = 'Gửi phản hồi thành công!';
//    const MSG_KHONG_NHAN_DIEN_THUE_BAO = 'Không nhận diện được thuê bao';
////    const MSG_USERNAME_ALREADY_EXIST = 'Tài khoản này đã được sử dụng để đăng ký, xin vui lòng kiểm tra lại.';
//    const MSG_USERNAME_ALREADY_EXIST = 'Tên tài khoản đã tồn tại, Quý khách vui lòng chọn một tên khác';
//    const MSG_USERNAME_BLOCK = 'Tài khoản này đã bị khóa, xin vui lòng kiểm tra lại.';
////    const MSG_DEVICE_NOT_EXIST = 'Thiết bị này không phải của VNPT Technology hoặc không dành cho thị trường này, xin vui lòng kiểm tra lại.';
//    const MSG_DEVICE_NOT_EXIST = 'Thiết bị không hợp lệ! Vui lòng liên hệ trung tâm hỗ trợ khách hàng.';
//    const MSG_NUMBER_ONLY = '{1} phải là kiểu number.';
//    const MSG_STRING_ONLY = '{1} phải là kiểu string.';
//    const MSG_ACCESS_DENNY = 'Bạn không có quyền thao tác với hệ thống vào hệ thống, token không đúng hoặc đã hết phiên đăng nhập';
////    const MSG_TOKEN_EXPRIED = 'Access Denny. Token hết phiên truy cập, vui lòng login lấy token mới để thao tác với hệ thông.';
//    const MSG_EXPIRED_SERVICE = 'Bạn chưa mua gói cước này hoặc gói cước đã hết hạn';
//    const MSG_SERVICE_NOT_CONTENT = 'Tạm thời không thể truy cập nội dung này';
//    const MSG_CANNOT_DELETE_ACTOR_DIRECTOR = '{1} đã được gán cho nội dung {2}. Không thể xóa';
//
//    const MSG_HOW_TO_LINK_PORTAL = 'http://103.31.126.223/api/web/index.php/payment-gate/charge-coin';
//    const MSG_HOW_TO_PURCHASE_CONTENT_SMS_CHANNEL = 'MUA <Mã nội dung> gửi 8x85';
//    const MSG_HOW_TO_PURCHASE_SERVICE_SMS_CHANNEL = 'MUAGOI <Mã gói cước> gửi 8x85';
////    const MSG_HOW_TO_PURCHASE_COIN_SMS_CHANNEL = 'NP gửi 8x85 (8585 – 5000đ – 5000 coin, 8685 – 10000đ – 10000 coin, 8785 – 15000đ – 15000 coin )';
//    const MSG_HOW_TO_PURCHASE_COIN_SMS_CHANNEL = 'Chức năng hiện thời chưa hoạt động, xin quý khách vui lòng thực hiện lại sau. Xin cảm ơn!';
//    const MSG_CONFIRM = 'Bạn có chắc chắn?';
//    const MSG_ADVENTISMENT = 'Karaoke là ứng dụng thuần Việt dành riêng cho sản phẩm SmartBox của VNPT Technology, cho phép bạn và gia đình có thể hát Karaoke ngay trên chính chiếc TV của mình mà không cần phải sắm thêm đầu Karaoke. Với giao diện đẹp mắt, màu sắc trẻ trung, dễ sử dụng cùng kho nhạc lớn, ứng dụng Karaoke này có đầy đủ các chức năng tương tự như đầu Karaoke thông thường, giúp người dùng có thể tìm bài hát theo tên, ca sĩ, tác giả hay số bài. Bạn chỉ cần bật internet lên, tìm cho mình bài hát yêu thích là có thể đắm mình trong những giai điệu tuyệt vời.';
//    const MSG_NOT_VALIDATE_DATE = 'Định dạng ngày không đúng ex: YYYY-MM-DD';
//    const MSG_TRANSFER_ERROR = 'Phân phối nội dung chưa thành công, nội dung chưa được phân phối đủ chất lượng video đến thị trường';
//    const MSG_TRANSFER_CONTENT_HAD_DOWNLOADED = 'Phân phối nội dung đã được phân phối đến thị trường';
//    const MSG_EXPORT_DATA_TO_FILE_SUCCESS = 'Hệ thống đang thực hiện gen file dữ liệu, quá trình này thực hiện mất thời gian. Xin vui lòng kiểm tra lại sau';

    public static function getSuccessMessage()
    {
        return Yii::t('app', 'Thành công.');
    }
    public static function getFileSuccessMessage()
    {
        return Yii::t('app', 'Hệ thống đang thực hiện gen file dữ liệu, quá trình này thực hiện mất thời gian. Xin vui lòng kiểm tra lại sau');
    }
    public static function getTranferContentMessage()
    {
        return Yii::t('app', 'Phân phối nội dung đã được phân phối đến thị trường');
    }
    public static function getTranferErrorMessage()
    {
        return Yii::t('app', 'Phân phối nội dung chưa thành công, nội dung chưa được phân phối đủ chất lượng video đến thị trường');
    }
    public static function getNotDateMessage()
    {
        return Yii::t('app', 'Định dạng ngày không đúng ex: YYYY-MM-DD');
    }
    public static function getAdventismentMessage()
    {
        return Yii::t('app', 'Karaoke là ứng dụng thuần Việt dành riêng cho sản phẩm SmartBox của VNPT Technology, cho phép bạn và gia đình có thể hát Karaoke ngay trên chính chiếc TV của mình mà không cần phải sắm thêm đầu Karaoke. Với giao diện đẹp mắt, màu sắc trẻ trung, dễ sử dụng cùng kho nhạc lớn, ứng dụng Karaoke này có đầy đủ các chức năng tương tự như đầu Karaoke thông thường, giúp người dùng có thể tìm bài hát theo tên, ca sĩ, tác giả hay số bài. Bạn chỉ cần bật internet lên, tìm cho mình bài hát yêu thích là có thể đắm mình trong những giai điệu tuyệt vời.');
    }
    public static function getConfirmMessage()
    {
        return Yii::t('app', 'Bạn có chắc chắn?');
    }
    public static function getSmsChaneMessage()
    {
        return Yii::t('app', 'MUA <Mã nội dung> gửi 8x85');
    }
    public static function getSmsCoinChaneMessage()
    {
        return Yii::t('app', 'Chức năng hiện thời chưa hoạt động, xin quý khách vui lòng thực hiện lại sau. Xin cảm ơn!');
    }
    public static function getSmsChaneServiceMessage()
    {
        return Yii::t('app', 'MUAGOI <Mã gói cước> gửi 8x85');
    }
    public static function getLinkPortalMessage()
    {
        return Yii::t('app', 'http://103.31.126.223/api/web/index.php/payment-gate/charge-coin');
    }
    public static function getDeleteActorMessage()
    {
        return Yii::t('app', ' đang được gắn với ');
    }
    public static function getAccessDennyMessage()
    {
        return Yii::t('app', 'Bạn không có quyền thao tác với hệ thống vào hệ thống, token không đúng hoặc đã hết phiên đăng nhập');
    }
    public static function getDeviceNotExitMessage()
    {
        return Yii::t('app', 'Thiết bị không hợp lệ! Vui lòng liên hệ trung tâm hỗ trợ khách hàng.');
    }
    public static function getNumberOnlyMessage()
    {
        return Yii::t('app', '{1} phải là kiểu number.');
    }
    public static function getBlockUsernameMessage()
    {
        return Yii::t('app', 'Tài khoản này đã bị khóa, xin vui lòng kiểm tra lại.');
    }
    public static function getExitsUsernameMessage()
    {
        return Yii::t('app', 'Tên tài khoản đã tồn tại, Quý khách vui lòng chọn một tên khác');
    }
    public static function getNotSeeSubscriberMessage()
    {
        return Yii::t('app', 'Không nhận diện được thuê bao');
    }
    public static function getFeedbackSuccessMessage()
    {
        return Yii::t('app', 'Gửi phản hồi thành công!');
    }
    public static function getNoCommentMessage()
    {
        return Yii::t('app', 'Không thành công. Quý khách vui lòng nhập lời bình.');
    }
    public static function getFavoriteFailMessage()
    {
        return Yii::t('app', 'Quý khách đã cập nhật danh sách yêu thích thất bại.');
    }
    public static function getFavoriteExitsMessage()
    {
        return Yii::t('app', 'Nội dung này đã nằm trong danh sách yêu thích của quý khách.');
    }
    public static function getUnFavoriteExitsMessage()
    {
        return Yii::t('app', 'Nội dung này không nằm trong danh sách yêu thích của quý khách.');
    }
    public static function getFavoriteSuccessMessage()
    {
        return Yii::t('app', 'Quý khách đã cập nhật danh sách yêu thích thành công.');
    }
    public static function getUnFavoriteSuccessMessage()
    {
        return Yii::t('app', 'Quý khách đã xóa thành công nội dung khỏi danh sách yêu thích.');
    }
    public static function getUpdateProfileMessage()
    {
        return Yii::t('app', 'Cập nhật thành công');
    }
    public static function getActionFailMessage()
    {
        return Yii::t('app', 'Không thành công. Quý khách vui lòng thử lại sau ít phút.');
    }
    public static function getSubscriberNotFoundMessage()
    {
        return Yii::t('app', 'Số điện thoại chưa đăng ký sử dụng dịch vụ');
    }
    public static function getSysDataMessage()
    {
        return Yii::t('app', 'Hệ thống bắt đầu phân phối nội dung đây là quá trình bất đồng bộ. Vui lòng kiểm tra lại sau');
    }
    public static function getNotFoundStreamMessage()
    {
        return Yii::t('app', 'Không tìm thấy streaming.');
    }
    public static function getNotFoungContentProfileMessage()
    {
        return Yii::t('app', 'Không tìm thấy chất lượng của nội dung này.');
    }
    public static function getNotFoundContentMessage()
    {
        return Yii::t('app', 'Không tìm thấy nội dung.');
    }
    public static function getNotFoundServiceMessage()
    {
        return Yii::t('app', 'Không tìm thấy gói cước.');
    }
    public static function getContentProfileNotFoundMessage()
    {
        return Yii::t('app', 'Content Profile không tồn tại');
    }
    public static function getContentProfileUpdateSuccessMessage()
    {
        return Yii::t('app', 'Cập nhật content profile thành công');
    }
    public static function getContentProfileDefaultNotFoundMessage()
    {
        return Yii::t('app', 'Nội dung không tồn tại Content profile ở thị trường default ');
    }
    public static function getPhoneWrongMessage()
    {
        return Yii::t('app', 'Số điện thoại đăng ký không hợp lệ, vui lòng kiểm tra lại.');
    }
    public static function getPhoneWrongLoginMessage()
    {
        return Yii::t('app', 'Số điện thoại đăng nhập không hợp lệ, vui lòng kiểm tra lại.');
    }
    public static function getChangePassSuccessMessage()
    {
        return Yii::t('app', 'Đổi mật khẩu thành công');
    }
    public static function getChangeOldPassFailMessage()
    {
        return Yii::t('app', 'Mật khẩu cũ không đúng, Quý khách vui lòng nhập lại');
    }
    public static function getWrongUserOrPassMessage()
    {
        return Yii::t('app', 'Thông tin tài khoản hoặc mật khẩu không hợp lệ');
    }
    public static function getNotFoundUserMessage()
    {
        return Yii::t('app', 'Người dùng không tồn tại');
    }
    public static function getNotEmptyMessage()
    {
        return Yii::t('app', '{1} không được phép để trống');
    }

    public static function getFailMessage()
    {
        return Yii::t('app', 'Không thành công. Vui lòng thử lại');
    }
    public static function getFailChangeStatusMessage()
    {
        return Yii::t('app', 'Lỗi hệ thống, không thể cập nhật trạng thái của nội dung phân phối. Vui lòng thử lại');
    }
    public static function getNotDataMessage()
    {
        return Yii::t('app', 'Không có dữ liệu');
    }
    public static function getErrorSystemMessage()
    {
        return Yii::t('app', 'Hệ thống hiện không thể thực hiện chức năng này, xin vui lòng quay lại sau');
    }
    public static function getFailPassMessage()
    {
        return Yii::t('app', 'Tên tài khoản hoặc mật khẩu chưa đúng. Vui lòng thử lại');
    }
    public static function getFailUserMessage()
    {
        return Yii::t('app', 'Tài khoản đang tạm dừng, vui lòng liên hệ với quản trị viên để được trợ giúp');
    }
    public static function getAddSuccessMessage()
    {
        return Yii::t('app', 'Thêm mới thành công');
    }
    public static function getUpdateSuccessMessage()
    {
        return Yii::t('app', 'Cập nhật thành công');
    }
    public static function getDeleteSuccessMessage()
    {
        return Yii::t('app', 'Xóa thành công');
    }
    public static function getLoginSuccessMessage()
    {
        return Yii::t('app', 'Quý khách đã đăng nhập thành công!');
    }
    public static function getSubscriberInactiveMessage()
    {
        return Yii::t('app', 'Tài khoản đang không thể sử dụng. Vui lòng liên hệ 1900 1525 nhánh 3 để có thêm thông tin chi tiết. Xin cám ơn!');
    }
    public static function getRegisterSuccessMessage()
    {
        return Yii::t('app', 'Quý khách đã đăng ký thành công tài khoản. Quý khách đã có thể sử dụng dịch vụ ngay từ lúc này');
    }
    public static function getTokenFailMessage()
    {
        return Yii::t('app', 'Mã xác thực không chính xác, xin vui lòng kiểm tra lại');
    }
    public static function getVerifyTrueMessage()
    {
        return Yii::t('app', 'Chúc mừng bạn đã đăng ký thành công tài khoản. Hệ thống sẽ tự động chuyển bạn về trang trước.');
    }
    public static function getNullValueMessage()
    {
        return Yii::t('app', 'Trường {1} bắt buộc nhập!');
    }
    public static function getNameFilm()
    {
        return Yii::t('app', 'Phim');
    }
    public static function getNameLive()
    {
        return Yii::t('app', 'Live');
    }
    public static function getNameMusic()
    {
        return Yii::t('app', 'Âm Nhạc');
    }
    public static function getNameNew()
    {
        return Yii::t('app', 'Tin tức');
    }
    public static function getNameClip()
    {
        return Yii::t('app', 'Clip');
    }
    public static function getNameKaraoke()
    {
        return Yii::t('app', 'Karaoke');
    }
    public static function getNameRadio()
    {
        return Yii::t('app', 'Radio');
    }
    public static function getNameLiveContent()
    {
        return Yii::t('app', 'Live Content');
    }
    public static function getNameStream()
    {
        return Yii::t('app', 'Chi tiết luồng');
    }

}