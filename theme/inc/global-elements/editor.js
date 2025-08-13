(function(wp){
    var __ = wp.i18n.__;
    var registerPlugin = wp.plugins.registerPlugin;
    var PluginToolbarButton = wp.editPost.PluginToolbarButton;
    var select = wp.data.select;
    var dispatch = wp.data.dispatch;
    var createBlock = wp.blocks.createBlock;
    var serialize = wp.blocks.serialize;
    var apiFetch = wp.apiFetch;
    var element = wp.element;

    function saveSelection(){
        var clientIds = select('core/block-editor').getSelectedBlockClientIds();
        if(!clientIds.length){
            alert(__('Select blocks to save.', 'fortiveax'));
            return;
        }
        var blocks = select('core/block-editor').getBlocksByClientId(clientIds);
        var content = serialize(blocks);
        var title = window.prompt(__('Global element title', 'fortiveax'));
        if(!title){ return; }
        apiFetch({
            path: '/wp/v2/fx_global',
            method: 'POST',
            data: { title: title, content: content, status: 'publish' }
        }).then(function(post){
            var block = createBlock('fortiveax/global-element', { id: post.id });
            dispatch('core/block-editor').replaceBlocks(clientIds, block);
        });
    }

    var SaveButton = function(){
        return element.createElement(PluginToolbarButton, {
            icon: 'admin-site',
            label: __('Save selection as Global', 'fortiveax'),
            onClick: saveSelection
        });
    };

    registerPlugin('fx-save-global', { render: SaveButton });
})(window.wp);