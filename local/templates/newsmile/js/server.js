var ServerCommand = function(code, data, success = null)
{
    this.restApiUrl = '/api/rest/';
    this.code = code;
    this.data = data;
    this.success = success;
};

$.extend(ServerCommand.prototype, {
    exec: function()
    {
        $.ajax({
            url: this.restApiUrl + this.code + '/',
            data: this.data,
            dataType: 'json',
            success: this.handleResponse.bind(this)
        });
    },

    handleResponse: function(response)
    {
        if((response.result === 'success') && (response.error === null))
        {
            if(typeof this.success === 'function')
            {
                this.success(response.data, this);
            }
        }
        else
        {
            console.warn('Произошла ошибка при выполнении серверной команды ' + this.code + '.');

            if(response.error && response.error.description)
            {
                console.warn(response.error.description);
            }
        }
    }
});
