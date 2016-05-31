(function () {
'use strict';

window.Requests = function(params) {
    this.base_href = params.base_href;
};

window.Requests.prototype = {
    base_href: '', //!< Base HREF to prepend to requests

    /**
     * Formats URL for request
     */
    url: function(request_id) {
        var url = this.base_href + '/requests';
        if (request_id) {
            url += '/' + request_id.toString();
        }
        return url;
    },

    /**
     * Get a request object from the server
     * \param request_id Request ID to retrieve, or null for all
     * \return Promise object for operation
     */
    get: function(request_id) {
        return $.getJSON(this.url(request_id));
    },

    /**
     * Update a request object to the server
     * \param request Request object to put
     * \return Promise object for operation
     */
    put: function(request) {
        return $.post({
            url: this.url(request_id),
            data: JSON.stringify(request),
            contentType: 'application/json; charset=UTF-8',
            processData: false,
        });
    },

    /**
     * Adds a new request object to the sever
     * \param request Request object to put
     * \return Promise object for operation
     */
    add: function(request) {
        delete request.id;
        return $.post({
            url: this.url(),
            data: JSON.stringify(request),
            contentType: 'application/json; charset=UTF-8',
            dataType: 'json',
            processData: false,
        });
    },

    /**
     * Attempts to update object. Upon failure will attempt to add new object.
     * \param request Request object to put
     * \return Promise object for operation
     */
    put_or_add: function(request) {
        if (request.id) {
            return this.put(request).fail(function(data) {
                if (data.status == 404) {
                    return this.add(request);
                }
            });
        } else {
            return this.add(request);
        }
    },
};

}());
