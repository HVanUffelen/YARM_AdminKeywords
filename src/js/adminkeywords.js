;(function ($) {
    var onready = function () {
        /* Setup for ajax*/
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('conFtent')
            }
        });
//Cleaner and edit ------------------------------------------------------------cleaner and edit

        // Manual keyword cleaning

        // Check if submit button is checked at the start
        actualiseKeywordCleanerForm();

        // Check if at least one radiobutton is selected
        function atLeastOneRadioKeyword() {
            const rbs = document.querySelectorAll('input[name="selected_keyword"]');
            for (const rb of rbs) {
                if (rb.checked) {
                    $(document).find('#selectKeywordError').prop('hidden', true);
                    return true;
                }
            }
            $(document).find('#selectKeywordError').prop('hidden', false);
            return false;
        };

        // Check if all fields are filled with at least 1 character
        function allIdsFilledKeywords() {
            var count = 0;
            var ids = $(document).find('.id');
            Array.prototype.forEach.call(ids, id => {
                if (id.value) {
                    count++;
                }
            });
            if (count === ids.length) {
                $(document).find('#keywordCleaningIdError').prop('hidden', true);
                return true;
            } else {
                $(document).find('#keywordCleaningIdError').prop('hidden', false);
                return false;
            }
        };


        // Update the submit button to be enabled / disabled
        function actualiseKeywordCleanerForm() {
            var radio = atLeastOneRadioKeyword();
            var ids = allIdsFilledKeywords();
            if (radio && ids) {
                $(document).find('#keywordCleaningErrors').prop('hidden', true);
                $(document).find('#btn-change-keyword').prop("disabled", false);
            } else {
                $(document).find('#keywordCleaningErrors').prop('hidden', false);
                $(document).find('#btn-change-keyword').prop("disabled", true);
            }
        };

        // Update on radiobutton check
        $(document).on('click', '.selected_keyword', function(e) {
            $(document).find('.bg-selected').removeClass('bg-selected');
            $(this).parent().addClass('bg-selected');
            actualiseKeywordCleanerForm();
        });

        // Update on  edit
        $(document).on('keyup', '.name', function(e) {
            actualiseKeywordCleanerForm();
        });

        // Add a field
        $(document).on('click', '#btn-add-field-keyword', function (e) {
            e.preventDefault();
            var keywords = $(document).find('.keyword');
            var clone = keywords.find('.keyword-clean-item:first').clone();
            clone.removeClass('bg-selected');
            clone.find('.selected_keyword').prop('checked', false);
            clone.find('.id').val(null);
            clone.find('.name').val('');
            clone.find('.name_id').val(null);
            clone.find('.translation').val('');
            clone.find('.ps-message').addClass('invisible');
            activateAutocompleteKeywordCleaner(clone.find('input.typeahead'));
            clone.find('.remove').prop('hidden', false);
            clone.find('.remove-placeholder').prop('hidden', true);
            clone.find('.keyword-search-loader').prop('hidden', true);
            clone.appendTo(keywords);
            udpateIndex(keywords,'.keyword-clean-item');
            actualiseKeywordCleanerForm();
        });

        // Remove a keyword
        $(document).on('click', '.remove', function (e) {
            e.preventDefault();
            var button = $(this);
            var keywords = button.parents('.keyword');
            var keyword = button.parents('.keyword-clean-item');
            keyword.remove();
            udpateIndex(keywords);
            actualiseKeywordCleanerForm();
            return false;
        });

        // Make autocomplete work
        var activateAutocompleteKeywordCleaner = function (input) {
            if (typeof input.data('autocomplete-url') === "undefined")
                return;
            // documentation
            // https://www.npmjs.com/package/bootstrap-3-typeahead
            input.typeahead({
                changeInputOnSelect: false,
                minLenght: 2,
                items: 'all',
                delay: 500,
                autoSelect: false,
                matcher: function (item) {
                    return true;
                },
                displayText: function (item) {
                    return item.name +(item.name_id!=null?' (Ps.)':'');
                },
                afterSelect: function (item) {
                    input.val(item.name);
                    input.parent('.keyword-clean-item').find('.name').val(item.name);
                    input.parent('.keyword-clean-item').find('.name_id').val(item.name_id);
                    input.parent('.keyword-clean-item').find('.translation').val(item.translation);
                    input.parent('.keyword-clean-item').find('.id').val(item.id);
                    if( item.name_id != null) input.parent('.keyword-clean-item').find('.ps-message').removeClass('invisible');
                    actualiseKeywordCleanerForm();
                },
                source: function (query, process) {
                    return $.get(input.data('autocomplete-url'), {query: query}, function (data) {
                        return process(data);
                    });
                }
            });
        };

        // To keep the index of the radiobuttons actual
        var udpateIndex = function (keywords,$keyClass) {
            index = 0;
            keywords.find($keyClass).each(function () {
                var keyword = $(this);
                keyword.find('.selected_keyword').val(index);
                index++;
            });
        };

        $(document).on('click', '#btn-split-keyword', function () {
            //add split attr
            $('#split-keyword').val('yes');
            //submit
            $('#keywordCleaning').submit();
        });
        $(document).on('click', '#btn-change-keyword', function () {
            //add split attr
            $('#split-keyword').val('no');
            //submit
            $('#keywordCleaning').submit();
        });

        $('input.auto-keyword-clean').each(function () {
            activateAutocompleteKeywordCleaner($(this));
        });

        //edit keyword
        $(document).on('click', '#edit-keyword', function (event) {

            event.preventDefault();
            var id = $(this).data('id');
            var redirect = $(this).data('redirect');
            $.get('keywords/' + id + '/editAjax', function (data) {
                $('.keyword-edit-modal-title').html("Edit keyword");
                $('#btn-save-keyword').html("Edit keyword");
                $('#keywordModal').modal('show');
                $('#id').val(data.data.id);
                $('#redirect').val(redirect);
                $('#name').val(data.data.name);
                $('#translation').val(data.data.translation);
                $('.author-switch').removeClass('d-inline-block');
                $('.edit-message').removeClass('d-none');
                $('.author-switch').addClass('d-none');
                $('#btn-save-keyword').addClass('edit');
            })
        });
        //save edit
        $(document).on('click', '#btn-save-keyword', function (event) {
            event.preventDefault();
            if (!($('#btn-save-keyword').hasClass('edit')===true)){
                $('#newKeywordForm').submit();
            } else {
                var id = $("#id").val();
                var redirect = $("#redirect").val();
                var name = $("#name").val();
                var translation = $("#translation").val();
                $.ajax({
                    url: 'keywords/updateAjax/' + id,
                    type: "PUT",
                    data: {
                        id: id,
                        name: name,
                        translation: translation,
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#redirect').val('1');
                        $('#newKeywordForm').trigger("reset");
                        $('#keywordModal').modal('hide');
                        window.location.href = YARM.base_url+'/dlbt/keywords?page='+redirect;

                    }
                });
            }
        });

        //delete confirmation
        $(document).on('click','.delete-keyword',function () {
            let keyword = $(this).data('keyword');
            let msg = `Are you sure you want to delete this keyword?`;
            msg += `\n ${keyword}`

            if(confirm(msg)) {
                $(this).closest('form').submit();
            }
        });

        //create new person
        if(window.location.href.includes('/dlbt/adminTables?Model=name&s=k')) $('#create-new').click();

        //helping vars
        var loader = '<div class="spinner-border text-primary m-auto" role="status">\n' +
            '                <span class="sr-only">Loading...</span>\n' +
            '                </div>';

    };
    $(document).ready(onready);
}(jQuery));
