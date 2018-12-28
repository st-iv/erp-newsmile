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
        return new Promise((resolve, reject) =>
        {
            $.ajax({
                url: this.restApiUrl + this.code + '/',
                data: this.data,
                dataType: 'json',
                complete: this.handleResponse.bind(this, resolve, reject)
            });
        });
    },

    handleResponse: function(resolve, reject, jqXHR, textStatus)
    {
        console.log(jqXHR, textStatus, 'test!!!');

        let response = jqXHR.responseJSON;

        if(textStatus === 'success' && (response.result === 'success') && (response.error === null))
        {
            if(typeof this.success === 'function')
            {
                this.success(response.data, this);
            }

            resolve(response.data);
        }
        else
        {
            console.warn('Произошла ошибка при выполнении серверной команды ' + this.code + '.');

            if(response && response.error && response.error.description)
            {
                console.warn(response.error.description);
                //reject(response.error);
            }
            else
            {
                //reject(jqXHR); //TODO вернуть объект error с той же сткруктурой, которую присылает сервер
            }
        }
    }
});
