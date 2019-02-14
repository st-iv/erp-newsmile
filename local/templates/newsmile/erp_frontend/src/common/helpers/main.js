import ColorHelper from './color-helper';
import CookieHelper from './cookie-helper';
import DateHelper from './date-helper';
import GeneralHelper from './general-helper';
import PhoneHelper from './phone-helper';
import StringHelper from './string-helper';

let Helper = Object.assign({
    Color: ColorHelper,
    Cookie: CookieHelper,
    Date: DateHelper,
    Phone: PhoneHelper,
    String: StringHelper,
}, GeneralHelper);

export default Helper;