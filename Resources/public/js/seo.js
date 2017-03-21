$(document).ready(function() {

    /** ================================================================
     *   Slug generator call in AJAX
     */

    var $slugField =    $("input[name$='[slug]']");
    var $slugGenerateButton = $("#generateSlug");

    // On generate button click
    $slugGenerateButton.click(function() {
        // Check required fields for slug are set
        var fields = JSON.parse($slugGenerateButton.attr('data-fields'));
        var fieldsValues = {};

        for(i in fields) {
            if ($("input[name$='[" + fields[i] + "]']").val() == "") {
                alert("Vous devez remplir tous les champs nécessaires pour générer le slug.");
                return;
            } else {
                fieldsValues[fields[i]] = $("input[name$='[" + fields[i] + "]']").val();
            }
        }

        var data = {};
        data['fields'] = fieldsValues;
        data['entityClass'] = $(this).attr('data-entity');

        $("div.errors p").addClass('hidden');
        $.ajax({
            url:  Routing.generate('lch_seo_generate_slug'),
            data : data,
            method : 'POST',
            success: function(result) {
                if(result.success){
                    $slugField.val(result.slug);
                } else {
                    $("div.errors p span.text").html(result.message);
                    $("div.errors p").removeClass('hidden');
                }
            }
        });
    });
    //
    // ================================================================
});