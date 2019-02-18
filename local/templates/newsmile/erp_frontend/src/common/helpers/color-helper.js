import tinycolor from 'tinycolor2'
let ColorHelper = {

    lighten: function(initColor, value)
    {
        let color = tinycolor(initColor),
            coef = 1 - Math.pow((color.getBrightness() / 255), 3);

        return color.lighten(value * coef).toString();
    },

    darken: function(initColor, value)
    {
        let color = tinycolor(initColor),
            coef = color.getBrightness() / 255;

        return color.darken(value * coef).toString();
    }
};

export default ColorHelper