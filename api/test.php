<html>
<head>
<style>
#item_template { display: none; }
#raw_data { font-family: monospace; }
#edit_request { display: none; }
</style>
</head>
<body>

<h1>Request list</h1>
<ul id="requests">
</ul>
QuoteNum: <input type="text" id="new_request_quote_num"><input id="new_request" type="button" value="Add">

<div id="edit_request">
<h1>Edit request</h1>
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
    <pre id="raw_data"></pre>
</div>
</div>

<script type="text/javascript" src="vendor/components/jquery/jquery.min.js"></script>
<script type="text/javascript" src="src/requests.js"></script>
<script type="text/javascript" src="src/request_form.js"></script>
<script type="text/javascript">
$(function() {
    window.getQueryParams = function(qs) {
        qs = qs.split('+').join(' ');

        var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;

        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
        }

        return params;
    };

    window.update_request_list = function() {
        window.requests.get()
            .done(function(data) {
                var ul = $('ul#requests');
                ul.children().remove();
                if (data.length > 0) {
                    $.each(data, function (index) {
                        var req = this;
                        a = $('<a href="#">'+req.QuoteNum+'</a>');
                        a.attr('data-req-id', req.id);
                        a.on('click', function(event) {
                            event.preventDefault();
                            window.request_id = $(this).attr('data-req-id');
                            window.history.pushState(req, 'Quote ' + req.QuoteNum, '?id=' + req.id);
                            window.get_request(window.request_id);
                            return false;
                        });
                        ul.append($('<li>').append(a));
                    });
                } else {
                    ul.append('<li>No requests</li>');
                }
            })
            .fail(function(data) {
                throw RangeError(data.responseJSON.errors[0].detail);
            });
    };

    window.onpopstate = function(e){
        if(e.state) {
            window.get_request(e.state.id);
        } else {
            this.get_request(-1);
        }
    };

    window.get_request = function(id) {
        if (id >= 0) {
            window.requests.get(id)
                .done(function(data) {
                    window.request_form.fromRequest(data[0]);
                    $('#raw_data').text(JSON.stringify(data[0], null, 2));
                    $('#edit_request').show();
                });
        } else {
            window.request_form.clear();
            $('#edit_request').hide();
            $('#raw_data').text('');
        }
    };

    window.query_params = window.getQueryParams(window.location.search);
    window.req_form = $('form#test');
    window.requests = new Requests({base_href: window.location.pathname.replace('test.php', '')});
    window.request_form = new RequestForm({
        form: window.req_form,
        item_row_template: $('#item_template'),
    });
    window.request_id = parseInt(window.query_params.id);

    $('#new_request').on('click', function(event) {
        event.preventDefault();
        window.request_form.clear();
        var req = window.request_form.toRequest();
        req.QuoteNum = $('#new_request_quote_num').val();
        window.requests.put_or_add(req)
            .done(function(data) {
                window.update_request_list();
                window.get_request(data[0].id);
            });
        return false;
    });

    window.req_form.on('submit', function(event) {
        event.preventDefault();
        var req = window.request_form.toRequest();

        window.requests.put_or_add(req)
            .done(function(data) {
                window.get_request(data[0].id);
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
            window.update_request_list();
            window.get_request(-1);
        });
    });

    $('#add_row').on('click', function(e) {
        window.request_form.addRow();
    });

    if (window.request_id >= 0) {
        window.get_request(window.request_id);
    }

    window.update_request_list();
});
</script>

</body>
</html>
