var TeethMatrix = function(selector)
{
    this.$node = $(selector);
    $(document).ready(this.init.bind(this));
};

$.extend(TeethMatrix.prototype, {
    init: function()
    {
        var self = this;
        
        this.$node.find('.js-teeth-matrix-select-group').change(function()
        {
            self.$node.find('.teeth-matrix__jaws').removeClass('selected');
            self.$node.find('.teeth-matrix__jaws[data-group-code=' + $(this).val() + ']').addClass('selected');
        });

        this.$node.find('.teeth-matrix__body').selectable({
            filter: '.jaw__tooth',
            stop: function()
            {
                var selectedTeeth = [];

                self.$node.find('.jaw__tooth.ui-selected').each(function()
                {
                    selectedTeeth.push($(this).data('number'));
                });

                self.$node.find('.js-teeth-matrix-input').val(selectedTeeth);
            }
        });

        this.$node.find('.js-select-jaw-button').click(function()
        {
            var jawCode = $(this).data('target');
            var $shownJawTeeth = self.$node.find('.teeth-matrix__jaws.selected .teeth-matrix__jaw[data-jaw-code=' + jawCode + '] .jaw__tooth');
            var $jawTeeth = self.$node.find('.teeth-matrix__jaw[data-jaw-code=' + jawCode + '] .jaw__tooth');

            if($shownJawTeeth.filter('.ui-selected').length === $shownJawTeeth.length)
            {
                // если все зубы отображаемой челюсти данного типа уже выбраны, то при нажатии на кнопку снимаем выделение (и с детских, и со взрослых)
                $jawTeeth.removeClass('ui-selected');
            }
            else
            {
                // иначе выделяем все зубы отображаемой челюсти
                $shownJawTeeth.addClass('ui-selected');
            }
        });
    }
});
