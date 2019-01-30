export default class Animation
{
    draw = null;
    animationFrameId = null;
    duration = null;
    params = null;
    lastFrameTime = null;

    constructor(drawHandler)
    {
        this.draw = drawHandler;
    }

    animate(duration = null, params = {})
    {
        this.start = performance.now();
        this.lastFrameTime = this.start;
        this.duration = duration;
        this.params = params;
        this.animationFrameId = window.requestAnimationFrame(this._animationFrameHandler.bind(this));
    }

    stop()
    {
        window.cancelAnimationFrame(this.animationFrameId);
    }

    _animationFrameHandler(time)
    {
        let timePassed = time - this.start;
        if(timePassed < 0) timePassed = 0;

        if (this.duration && (timePassed > this.duration)) timePassed = this.duration;

        if ((this.draw(timePassed, time - this.lastFrameTime, this.params) !== false) && (!this.duration || (timePassed <= this.duration)))
        {
            this.animationFrameId = window.requestAnimationFrame(this._animationFrameHandler.bind(this));
        }

        this.lastFrameTime = time;
    }
}