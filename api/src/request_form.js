(function () {
'use strict';

window.RequestForm = function(params) {
    this.form = $(params.form);
    this.item_row_template = $(params.item_row_template);

    this.init();
};

window.RequestForm.prototype = {
    form: null,
    item_row_template: null,

    init: function() {
    },

    toRequest: function() {
        var request_form = this;
        var req = request_form.structFromInputs(request_form.form.find('input').not('#items input'));
        req.items = [];
        request_form.form.find('#items').find('.item').each(function() {
            var item_row = $(this);
            var item = request_form.structFromInputs(item_row.find('input'));
            req.items.push(item);
        });
        return req;
    },

    fromRequest: function(req) {
        var request_form = this;

        request_form.clear();

        request_form.fillInputsFromStruct(request_form.form.find('input').not('#items input'), req);

        var items = request_form.form.find('#items');
        $.each(req.items, function() {
            var item = this;
            var item_row = request_form.newItemRow(request_form.item_row_template);
            request_form.fillInputsFromStruct(item_row.find('input'), item);
            items.append(item_row);
        });
    },

    filterInputs: function(inputs) {
        return inputs.filter(function (index, element) {
            var input = $(element);
            if (input.attr('type') == 'submit' || input.attr('type') == 'reset' || input.attr('type') == 'button') {
                return false;
            }
            return true;
        });
    },

    structFromInputs: function(inputs) {
        var request_form = this;
        var struct = {};
        request_form.filterInputs(inputs).each(function () {
            var input = $(this);
            struct[input.attr('name')] = input.val();
        });
        return struct;
    },

    fillInputsFromStruct: function(inputs, struct) {
        $.each(struct, function(key, val) {
            inputs.filter('input[name="'+key+'"]')
                .val(val)
                .attr('value', val);
        });
    },

    newItemRow: function() {
        var item_row = this.item_row_template.clone();
        item_row.removeAttr('id');
        item_row.addClass('item');
        item_row.find('#delete_item').on('click', function (e) {
            $(this).closest('.item').remove();
        });
        return item_row;
    },

    addRow: function() {
        var item_row = this.newItemRow();
        this.form.find('#items').append(item_row);
    },

    clear: function() {
        var request_form = this;
        request_form.form.find('#items').children().remove();
        request_form.filterInputs(request_form.form.find('input')).each(function () {
            $(this).val('').attr('value', '');
        });
    },
};

}());
