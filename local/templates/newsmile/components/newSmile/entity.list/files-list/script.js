var PatientFilesList = function(rootElement, patientId)
{
    this.$root = ((typeof rootElement === 'string') ? $(rootElement) : rootElement);
    this.$addFileInput = this.$root.find('.js-patient-card-add-file');
    this.patientId = patientId;

    this.groupBy = '';
    this.sortBy = 'DETAIL'

    this.init();
};

$.extend(PatientFilesList.prototype, {
    init: function()
    {
        var self = this;

        this.isEmpty = this.$root.find('.file').length === 0;

        // init add file field
        this.$addFileInput.change(function(e)
        {
            if(e.target.files.length)
            {
                self.openFileEditPopup(e.target.files[0]);
            }
        });

        this.initFileTable();

        // init file info hiding
        this.$root.find('.file-info').click(function()
        {
            $(this).toggleClass('hidden');
        });

        // init file filter field
        this.$root.find('.js-files-filter-type').change(function ()
        {
            var showTypes = $(this).val();
            var isAllEnabled = (showTypes.indexOf('ALL') !== -1);

            self.$root.find('.table__cell--files').each(function()
            {
                var filesCount = 0;

                $(this).find('.file').each(function()
                {
                    if(!isAllEnabled && (showTypes.indexOf($(this).data('type-code')) === -1))
                    {
                        $(this).hide();
                    }
                    else
                    {
                        $(this).show();
                        filesCount++;
                    }
                });

                var $group = $(this).closest('.table__row').prev('.table__row--group');

                if(filesCount)
                {
                    //$(this).show();
                    $group.show();
                    $group.find('.js-files-count').text(filesCount);
                }
                else
                {
                    $(this).hide();
                    $group.hide();
                }
            });
        });

        // init grouper
        var $grouper = this.$root.find('.js-files-grouper');

        $grouper.change(function()
        {
            self.groupFiles($(this).val());
        });

        this.groupBy = $grouper.val();
    },

    /**
     * Инициализация списка файлов
     */
    initFileTable: function()
    {
        var self = this;
        var $filesTable = this.$root.find('.files__table');

        this.$root.find('.js-files-filter-type').trigger('change');

        // init file preview change
        this.$root.find('.files__table .file').click(function (e)
        {
            var $file = $(this);

            if(!$file.hasClass('selected'))
            {
                self.showFileDetail($file);
            }

            e.preventDefault();
        });

        this.showFileDetail($filesTable.find('.file.selected'));


        // files context menu
        $.contextMenu({
            selector: '.js-patient-file-list-file',
            items: {
                edit: {
                    name: 'Изменить свойства',
                    callback: function()
                    {
                        self.openFileEditPopup(null, $(this).data('id'));
                    }
                },
                download: {
                    name: 'Скачать',
                    callback: function()
                    {
                        var downloadLink = document.createElement('a');
                        downloadLink.href = String($(this).data('download'));
                        downloadLink.setAttribute('download', String($(this).data('original-name')));
                        downloadLink.click();
                    }
                }
            }
        });

        // files list row toggle
        this.$root.find('.table__row--group').click(function()
        {
            $(this).next().find('.table__cell').slideToggle();
        });

        this.$root.find('.table__cell--files').each(function()
        {
            var $this = $(this);

            if($this.find('.file.selected').length > 0)
            {
                $(this).show();
            }
            else
            {
                $(this).hide();
            }
        });

        var $toggleSort = $filesTable.find('.js-toggle-sort');

        this.sortOrder = $toggleSort.data('sort-order');

        if(!this.sortOrder)
        {
            this.sortOrder = 'desc';
        }

        $toggleSort.click(function()
        {
            self.sortOrder = ((self.sortOrder === 'asc') ? 'desc' : 'asc');
            self.updateFileTable();
        });
    },

    groupFiles: function(fieldCode)
    {
        this.groupBy = fieldCode;
        this.updateFileTable();
    },

    /**
     * Открывает окно создания / редактирования файла
     * @param file - файл для отображения
     * @param fileId - id редактируемого файла
     */
    openFileEditPopup: function(file, fileId = 0)
    {
        var self = this;

        Ajax.loadPopup(
            '/ajax/patient-card/edit-patient-file.php',
            'patient-file-edit',
            {
                'PATIENT_ID': self.patientId,
                'FILE_ID': fileId
            },
            this.initFileEditPopup.bind(this, file),
            'js-patient-file-edit'
        );
    },

    /**
     * Инициализация окна добавления / редактирования файла
     * @param contentHtml
     * @param $popup
     */
    initFileEditPopup: function(file, contentHtml, $popup)
    {
        var $form = $popup.find('form');
        var self = this;

        // init save button
        $popup.find('.js-patient-file-save-button').click(function()
        {
            $form.submit();
        });

        // init preview image
        if(file !== null)
        {
            var reader = new FileReader();
            reader.onload = function(e)
            {
                var $preview = $popup.find('.js-patient-file-edit-preview');
                var $noPreviewNote = $popup.find('.js-patient-file-edit-no-preview');

                if(file.type.match(/image.*/))
                {
                    $preview.attr('src', e.target.result);
                    $preview.show();
                    $noPreviewNote.hide();
                }
                else
                {
                    $preview.hide();
                    $noPreviewNote.show();
                }
            };

            reader.readAsDataURL(file);
        }

        // init form ajax load
        $form.submit(function(e)
        {
            var data = new FormData($form[0]);
            var fileFieldName = $form.find('.js-file-input-field').attr('name');

            if(file !== null)
            {
                data.set(fileFieldName, file);
            }

            Ajax.loadNode($form, data, function()
            {
                if(self.isEmpty)
                {
                    self.update();
                }
                else
                {
                    self.updateFileTable();
                }

                window.popupManager.close();
                self.$addFileInput.val('');
            });

            e.preventDefault();
        });
    },

    getQueryData: function()
    {
        return {
            PATIENT_ID: this.patientId,
            FILES_GROUP_BY: this.groupBy,
            FILES_SORT_ORDER: this.sortOrder
        };
    },

    /**
     * Обновление таблицы файлов
     */
    updateFileTable: function()
    {
        var data = this.getQueryData();

        var self = this;

        var $form = this.$root.find('.js-files-list-filter-form');
        Ajax.loadNode($form, data, function(response, contentHtml)
        {
            self.$root.find('.files__table').html($(contentHtml).find('.files__table').html());
            self.initFileTable();
        }, '');
    },

    update: function()
    {
        var data = this.getQueryData();

        Ajax.loadNode(this.$root, data, this.init.bind(this));
    },

    /**
     * Отображает файл детально
     * @param $file - jquery объект файла в таблице
     */
    showFileDetail: function($file)
    {
        var $filePreview = this.$root.find('.js-files-preview');
        var $fileNoPreviewNote = this.$root.find('.js-files-no-preview');

        var $fileInfo = this.$root.find('.file-info');

        if($file.length)
        {
            $filePreview.show();
            $fileInfo.show();
        }
        else
        {
            $filePreview.hide();
            $fileInfo.hide();
            return;
        }

        var detailPictureSrc = $file.data('detail-picture');

        if(detailPictureSrc)
        {
            $filePreview.show();
            $filePreview.attr('src', detailPictureSrc);
            this.$root.find('.preview').removeClass('no-image');
        }
        else
        {
            $filePreview.hide();
            this.$root.find('.preview').addClass('no-image');
        }

        this.$root.find('.files__table .file').removeClass('selected');
        $file.addClass('selected');

        this.$root.find('.file-info__field').each(function ()
        {
            var $this = $(this);
            var fieldCode = $this.data('field-code');
            var fieldDataAttrName = 'field-' + fieldCode.toLowerCase().replace('_', '-');
            var fieldValue = $file.data(fieldDataAttrName);

            if(fieldValue)
            {
                $this.find('.field-value').text(fieldValue);
                $this.show();
            }
            else
            {
                $this.hide();
            }
        });
    },
});