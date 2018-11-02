var PatientsList = function(selector)
{
    this.$list = $(selector);
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
    this.$addFileInput = this.$popup.find('.js-patient-card-add-file');
    this.patientId = patientId;
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
                var targetTab = $this.data('target');

                $('.card__main-content .card__tab-1lvl').removeClass('active');
                $this.addClass('active');
                $('.card__main-content .card__tab-content').removeClass('active');
                $('.card__main-content .card__tab-content[data-tab-code=' + targetTab + ']').addClass('active');
            }
        });

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


        // init add file field
        this.$addFileInput.change(function(e)
        {
            if(e.target.files.length)
            {
                var $this = $(this);
                Ajax.loadPopup(
                    '/ajax/edit-patient-file.php',
                    Ajax.getAreaCode($this),
                    {
                        'PATIENT_ID': self.patientId
                    },
                    self.initFileEditPopup.bind(self, e.target.files[0]),
                    'js-patient-file-edit'
                );
            }
        });
    },

    initFileEditPopup: function(file, contentHtml, $popup)
    {
        var self = this;

        if (file.type.match('image.*'))
        {
            var reader = new FileReader();
            reader.onload = function(e)
            {
                $popup.find('.js-patient-file-edit-preview').attr('src', e.target.result);
            };

            reader.readAsDataURL(file);
        }

        var $form = $popup.find('form');

        $popup.find('.js-patient-file-save-button').click(function()
        {
            $form.submit();
        });

        $form.submit(function(e)
        {
            var data = new FormData($form[0]);
            var fileFieldName = $form.find('.js-file-input-field').attr('name');

            data.set(fileFieldName, self.$addFileInput.prop('files')[0]);

            console.log(data);
            Ajax.loadNode($form, data, function()
            {
                window.popupManager.close();
                self.$addFileInput.val('');
            });

            e.preventDefault();
        });
    }
});