import ColorHelper from 'js/helpers/color-helper';
import CookieHelper from 'js/helpers/cookie-helper';
import DateHelper from 'js/helpers/date-helper';
import GeneralHelper from 'js/helpers/general-helper';
import PhoneHelper from 'js/helpers/phone-helper';
import StringHelper from 'js/helpers/string-helper';

let Helper = Object.assign({
    Color: ColorHelper,
    Cookie: CookieHelper,
    Date: DateHelper,
    Phone: PhoneHelper,
    String: StringHelper,
}, GeneralHelper);

export default Helper;