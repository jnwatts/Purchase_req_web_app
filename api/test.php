<?
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = null;
}
?>
<html>
<body>
<script type="text/javascript" src="https://code.jquery.com/jquery-2.2.3.min.js"></script>

<?if (!$id) {?>
<ul id="requests">
</ul>
<form action="api/requests" method="post" id="test">
<input type="input" name="QuoteNum" value="">
<input type="submit" value="Add">
</form>
<div>
    <h3>Raw data</h3>
    <pre id="raw-data"></pre>
</div>
<script type="text/javascript">
$(function() {
    var form = $('form#test');
    form.on('submit', function(event) {
        event.preventDefault();
        var req = req_from_form();
        put_request(req)
            .done(function(data) {
                update_request_list();
                form[0].reset();
            })
            .fail(function(data) {
                alert(data.responseJSON.errors[0].detail);
            });
        return false;
    });

    update_request_list();
});

window.update_request_list = function() {
    $.getJSON("requests")
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
<form action="api/requests" method="post" id="test">
<input type="hidden" name="id">
<input type="input" name="QuoteNum">
<input type="submit" value="Save">
<input type="reset" value="Reset">
<input type="button" value="Delete">
</form>
<div>
    <h3>Raw data</h3>
    <pre id="raw-data"></pre>
</div>
<script type="text/javascript">
$(function() {
    window.request_id = <?=$id?>;

    get_request(request_id)
        .done(function(data) {
            $('#raw-data').text(JSON.stringify(data[0], null, 2));
        });
    var form = $('form#test');
    form.on('submit', function(event) {
        event.preventDefault();
        var req = req_from_form();
        put_request(req)
            .fail(function(data) {
                alert(data.responseJSON.errors[0].detail);
            });
        return false;
    });
    form.children('[value="Delete"]').on('click', function (e) {
        $.get({
            url: 'requests/' + request_id,
            method: 'DELETE',
        })
        .done(function() {
            window.location.replace('<?=$_SERVER['PHP_SELF']?>');
        });
    });
});
</script>
<?}?>

<script type="text/javascript">
window.req_from_form = function() {
    var req = {};
    var form = $('form#test');
    form.children('input').each(function (index) {
        var input = $(this);
        if (input.attr('type') == 'submit' || input.attr('type') == 'reset' || input.attr('type') == 'button') {
            return;
        }
        req[input.attr('name')] = input.val();
    });
    return req;
};

window.form_from_req = function(req) {
    var form = $('form#test');
    $.each(req, function(key, val) {
        form.children('input[name="'+key+'"]')
            .val(val)
            .attr('value', val);
    });
};

window.get_request = function(req) {
    var form = $('form#test');
    return $.getJSON("requests/"+req)
        .done(function(data) {
            form_from_req(data[0]);
        });
};

window.put_request = function(req) {
    if (req.id) {
        return $.post({
            url: "requests/"+req.id,
            data: JSON.stringify(req),
            contentType: "application/json; charset=UTF-8",
            processData: false,
        })
        .fail(function(data) {
            if (data.status == 404) {
                return add_request(req);
            }
        });
    } else {
        return add_request(req);
    }
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
</script>
</body>
</html>
