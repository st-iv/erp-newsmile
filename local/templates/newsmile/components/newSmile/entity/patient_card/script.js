var PatientsList = function(selector, params)
{
    this.$list = $(selector);
    this.params = params;
    this.filesList = null;

    $(document).ready(this.init.bind(this));
};

$.extend(PatientsList.prototype, {
    init: function()
    {
        var self = this;

        this.$list.find('tr.entity-list__row').click(function()
        {
            var $this = $(this);
            Ajax.loadPopup(
                $this.data('view-url'),
                Ajax.getAreaCode($this),
                {},
                function()
                {
                    window.patientCard = new PatientCard('.patient-card-popup', Number($this.data('id')))
                },
                'patient-card-popup'
            );
        });
    }
});


var PatientCard = function(selector, patientId)
{
    this.$popup = $(selector);
    this.patientId = patientId;

    this.initedTabs = [];
    this.init();
};


$.extend(PatientCard.prototype, {

    init: function()
    {
        var self = this;

        // init 1 lvl tabs
        this.$popup.find('.card__main-content .card__tab-1lvl').click(function(e)
        {
            var $this = $(e.target);
            if(!$this.hasClass('active'))
            {
                self.showTab($this);
            }
        });

        // init first tab
        this.showTab(this.$popup.find('.card__tab-1lvl.active'));

        // init edit mode button
        this.$popup.find('.js-toggle-edit-mode').click(function(e)
        {
            var $this = $(e.target);
            var url = '';
            var $activeTabContent = $('.card__tab-content.active');
            var editAreaCode = Ajax.getAreaCode($activeTabContent.children());

            var newTitle = $this.data('alt-title');
            $this.data('alt-title', $this.text());
            $this.text(newTitle);

            if($this.data('mode') == 'edit')
            {
                $this.data('mode', 'view');
                url = $activeTabContent.data('edit-url');
            }
            else
            {
                $this.data('mode', 'edit');
                url = $activeTabContent.data('view-url');
            }

            Ajax.load(url, editAreaCode);
        });
    },

    /**
     * Отображение вкладки
     * @param $tab - jQuery объект вкладки
     */
    showTab: function($tab)
    {
        var targetTab = $tab.data('target');
        var $tabBody = this.$popup.find('.card__main-content .card__tab-content[data-tab-code=' + targetTab + ']');

        this.$popup.find('.card__main-content .card__tab-1lvl').removeClass('active');
        $tab.addClass('active');
        this.$popup.find('.card__main-content .card__tab-content').removeClass('active');
        $tabBody.addClass('active');

        if(this.initedTabs.indexOf(targetTab) === -1)
        {
            this.initTab(targetTab, $tab, $tabBody);
            this.initedTabs.push(targetTab);
        }
    },

    /**
     * Инициализация содержимого вкладки (вызывается при первом открытии вкладки)
     * @param tabCode
     * @param $tab
     * @param $tabBody
     */
    initTab: function(tabCode, $tab, $tabBody)
    {
        var ajaxArea = Ajax.getAreaCode($tabBody, false);
        var self = this;

        if(ajaxArea)
        {
            var data = {
                PATIENT_ID: this.patientId
            };

            Ajax.loadNode($tabBody, data, function()
            {
                self.filesList = new PatientFilesList($tabBody, self.patientId);
            });
        }
    },
});