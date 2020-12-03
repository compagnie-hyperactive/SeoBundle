$(document).ready(function() {

    /** ================================================================
     *   Slug generator call in AJAX
     */

    var $slugField =    $("input.slug");
    var $slugGenerateButton = $("#generateSlug");
    var defaultLanguage = 'fr';

    if($slugGenerateButton.length === 1) {
        var fields = JSON.parse($slugGenerateButton.attr('data-fields'));

        /**
         * Check if required field for slug is in DOM, to display slug 'generation' button
         */
        for (i in fields) {
            if ($("input[name$='[" + fields[i] + "]']").length == 0) {
                $slugGenerateButton.addClass("hidden");
            }
        }


        // On generate button click
        $slugGenerateButton.click(function () {
            // Check required fields for slug are set
            var fieldsValues = {};

            for (i in fields) {
                if ($("input[name$='[" + fields[i] + "]']").val() == "") {
                    alert("Vous devez remplir tous les champs nécessaires pour générer le slug.");
                    return;
                } else {
                    fieldsValues[fields[i]] = $("input[name$='[" + fields[i] + "]']").val();
                }
            }

            $("#seo-slug .error-block").addClass('hidden');

            var data = {};
            data['fields'] = fieldsValues;
            data['entityClass'] = $(this).attr('data-entity');
            var languageAttribute = $(this).attr('data-language');
            if (typeof languageAttribute !== typeof undefined && languageAttribute !== false) {
                data['language'] = languageAttribute === "" ? ($('[data-lang-watch]').data('langWatch') || null) : languageAttribute
            }
            var idAttribute = $(this).attr('data-id');
            if (typeof idAttribute !== typeof undefined && idAttribute !== false) {
                data['id'] = idAttribute
            }

            $.ajax({
                url: $slugGenerateButton.attr('data-route-generation-path'),
                data: data,
                method: 'POST',
                success: function (result) {
                    if (result.success) {
                        $slugField.val(result.slug);
                    } else {
                        $("#seo-slug .error-block span").html(result.message);
                        $("#seo-slug .error-block").removeClass('hidden');
                    }
                }
            });
        });
    }
    //
    // ================================================================
});