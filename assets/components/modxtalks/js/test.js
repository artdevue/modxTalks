$.ajax({
    url: '/assets/components/modxtalks/connectors/connector.php',
    type: 'POST',
    headers: {Action:'add'},
    data: {
        title: '',
        email: 'aaa',
        name: '',
        conversation: 'resource-2',
        content: ''
        ctx: 'web'
    },
    success: function(data) {
        var result = $.parseJSON(data);
        if (result.status == 'false') {
            var data = result.data;
            for (var i = data.length - 1; i >= 0; i--) {
                console.log(data[i].msg)
            }
        }
    }
})