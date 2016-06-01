<?
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = null;
}
?>
<html>
<head>
<style>
#item_template { display: none; }
</style>
</head>
<body>
<script type="text/javascript" src="vendor/components/jquery/jquery.min.js"></script>
<script type="text/javascript" src="src/requests.js"></script>

<?if (!$id) {?>
<ul id="requests">
</ul>
<form action="api/requests" method="post" id="test">
QuoteNum: <input type="text" name="QuoteNum" value="">
<input type="submit" value="Add">
</form>
<div>
    <h3>Raw data</h3>
    <pre id="raw-data"></pre>
</div>
<script type="text/javascript">
$(function() {
    window.requests = new Requests({base_href: window.location.pathname.replace('test.php', '')});
    window.req_form = $('form#test');
    window.req_form.on('submit', function(event) {
        event.preventDefault();
        var req = req_from_form(window.req_form);
        window.requests.put_or_add(req)
            .done(function(data) {
                update_request_list();
                req_form[0].reset();
            })
            .fail(function(data) {
                alert(data.responseJSON.errors[0].detail);
            });
        return false;
    });

    update_request_list();

});

window.update_request_list = function() {
    window.requests.get()
        .done(function(data) {
            var ul = $('ul#requests');
            ul.children().remove();
            if (data.length > 0) {
                $.each(data, function (index) {
                    var li = $('<li>');
                    li.append('<a href="<?=$_SERVER['PHP_SELF']?>?id='+this.id+'">'+this.QuoteNum+'</a>');
                    ul.append(li);
                });
            } else {
                ul.append('<li>No requests</li>');
            }
            $('#raw-data').text(JSON.stringify(data, null, 2));
        })
        .fail(function(data) {
            throw RangeError(data.responseJSON.errors[0].detail);
        });
};
</script>
<?} else {?>
<div id="item_template" class="item">
    Part Number: <input type="text" name="Part_Number">
    <input type="button" value="Delete" id="delete_item">
</div>
<form action="api/requests" method="post" id="test">
<input type="hidden" name="id">
QuoteNum: <input type="text" name="QuoteNum">
<h2>Items</h2>
<input type="button" value="Add row" id="add_row">
<div id="items">
</div>
<hr width="200px" align="left">
<input type="submit" value="Save">
<input type="reset" value="Reset">
<input type="button" value="Delete" id="delete_request">
</form>
<div>
    <h3>Raw data</h3>
    <pre id="raw-data"></pre>
</div>
<script type="text/javascript">
$(function() {
    window.requests = new Requests({base_href: window.location.pathname.replace('test.php', '')});
    window.request_id = <?=$id?>;
    window.req_form = $('form#test');

    window.requests.get(request_id)
        .done(function(data) {
            form_from_req(window.req_form, data[0]);
            $('#raw-data').text(JSON.stringify(data[0], null, 2));
        });
    window.req_form.on('submit', function(event) {
        event.preventDefault();
        var req = req_from_form($(this));

        window.requests.put_or_add(req)
            .done(function(data) {
                form_from_req(window.req_form, data[0]);
                $('#raw-data').text(JSON.stringify(data[0], null, 2));
            })
            .fail(function(data) {
                alert(data.responseJSON.errors[0].detail);
            });
        return false;
    });
    window.req_form.find('#delete_request').on('click', function (e) {
        $.get({
            url: 'requests/' + request_id,
            method: 'DELETE',
        })
        .done(function() {
            window.location.replace('<?=$_SERVER['PHP_SELF']?>');
        });
    });

    $('input#add_row').on('click', function(e) {
        add_row(req_form);
    });
});
</script>
<?}?>

<script type="text/javascript">
window.create_item_row = function(form) {
    var item_div = $('#item_template').clone();
    item_div.removeAttr('id');
    item_div.find('#delete_item').on('click', function (e) {
        $(this).closest('.item').remove();
    });
    return item_div;
};
window.add_row = function(form) {
    var items_div = form.children('#items');
    items_div.append(create_item_row(form));
};

window.struct_from_inputs = function(inputs) {
    var struct = {};
    inputs.each(function (index) {
        var input = $(this);
        if (input.attr('type') == 'submit' || input.attr('type') == 'reset' || input.attr('type') == 'button') {
            return;
        }
        struct[input.attr('name')] = input.val();
    });
    return struct;
};

window.inputs_from_struct = function(container, struct) {
    $.each(struct, function(key, val) {
        container.find('input[name="'+key+'"]')
            .val(val)
            .attr('value', val);
    });
};

window.req_from_form = function(form) {
    var req = struct_from_inputs(form.find('input').not('#items input'));
    req.items = [];
    form.children('#items').children('.item').each(function() {
        var item = struct_from_inputs($(this).find('input'));
        req.items.push(item);
    });
    return req;
};

window.form_from_req = function(form, req) {
    var items_div = form.children('#items')
    items_div.children().remove();

    inputs_from_struct(form, req);

    $.each(req.items, function(key, val) {
        var item_div = create_item_row(form);
        inputs_from_struct(item_div, val);
        items_div.append(item_div);
    });

};

window.get_request = function(req) {
    var form = window.req_form;
    return $.getJSON("requests/"+req)
};

window.add_request = function(req) {
    delete req.id;
    return $.post({
        url: "requests",
        data: JSON.stringify(req),
        contentType: "application/json; charset=UTF-8",
        dataType: 'json',
        processData: false,
    });
};

(function($){
    $.fn.extend({
        getFullPath: function(stopAtBody){
            stopAtBody = stopAtBody || false;
            function traverseUp(el){
                var o = $(el);
                var result = o.prop('tagName');
                var pare = o.parent()[0];
                if (o.attr('id')) {
                    result += '#' + o.attr('id');
                }
                if (o.attr('class')) {
                    result += '.' + o.attr('class');
                }
                if (o.attr('name')) {
                    result += '[name=\''+o.attr('name')+'\']';
                }
                if (pare && pare.tagName !== undefined && (!stopAtBody || pare.tagName !== 'BODY')){
                    result = [traverseUp(pare), result].join(' ');
                }
                return result;
            };
            return this.length > 0 ? traverseUp(this[0]) : '';
        }
    });
})(jQuery);
</script>
</body>
</html>
