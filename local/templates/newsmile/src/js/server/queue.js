export default class Queue
{
    commands = [];
    engaged = false;

    constructor(successExecHandler, accumulate = false, timeout = 0)
    {
        this.successExecHandler = successExecHandler;
        this.accumulate = accumulate;
        this.timeout = timeout;
    }

    push(command)
    {
        if(!this.engaged && !this.commands.length)
        {
            this._execCommand(command);
        }
        else
        {
            if(this.accumulate)
            {
                this.commands.push(command);
            }
            else
            {
                this.commands = [command];
            }
        }
    }

    _handleExecResult(isSuccess, response)
    {
        if(isSuccess)
        {
            this.successExecHandler(response);
        }

        if(this.commands.length)
        {
            const nextCommand = this.commands.pop();
            const waitingTime = this.timeout - (Date.now() - this.lastExecTime);

            if(waitingTime > 0)
            {
                setTimeout(this._execCommand.bind(nextCommand), waitingTime);
            }
            else
            {
                this._execCommand(nextCommand);
            }
        }
        else
        {
            this.engaged = false;
        }
    }

    _execCommand(command)
    {
        this.engaged = true;
        this.lastExecTime = Date.now();
        command.exec().then(this._handleExecResult.bind(this, true), this._handleExecResult.bind(this, false));
    }
}