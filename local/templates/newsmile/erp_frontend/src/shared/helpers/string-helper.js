let StringHelper = {

    insert: function(substr, target, pos)
    {
        return target.substr(0, pos) + substr + target.substr(pos);
    }
};

export default StringHelper