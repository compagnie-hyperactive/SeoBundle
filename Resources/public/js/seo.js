$(document).ready(function() {

    /** ================================================================
     *   Slug generator call in AJAX
     */

    var $slugField =    $("#seo-slug input[name$='[slug]']");
    var $slugGenerateButton = $("#generateSlug");

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