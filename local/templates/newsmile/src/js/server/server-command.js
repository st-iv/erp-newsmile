export default class ServerCommand
{
    restApiUrl = '/api/rest/';

    constructor(code, data, success = null)
    {
        this.code = code;
        this.data = data;
        this.success = success;
    }
    exec()
    {
        return new Promise((resolve, reject) =>
        {
            $.ajax({
                url: this.restApiUrl + this.code + '/',
                data: this.data,
                dataType: 'json',
                complete: this._handleResponse.bind(this, resolve, reject)
            });
        });
    }

    _handleResponse(resolve, reject, jqXHR, textStatus)
    {
        let response = jqXHR.responseJSON;
        let self = this.constructor;

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
                reject(response.error);
            }
            else
            {
                reject(jqXHR); //TODO вернуть объект error с той же структурой, которую присылает сервер
            }//
        }

        if(this.queueCode && self.queues[this.queueCode])
        {
            let nextQueueItem = self.queues[this.queueCode].pop();
            nextQueueItem.command._send(nextQueueItem.resolve, nextQueueItem.reject);
        }
    }
}