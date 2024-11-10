jQuery(document).ready(function($) {
    $('.custom-subcategory').change(function() {
        let selectedCategories = [];
        
        // ذخیره دسته‌های انتخاب‌شده در آرایه
        $('.custom-subcategory:checked').each(function() {
            selectedCategories.push($(this).val());
        });

        $.ajax({
            url: cpf_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'cpf_fetch_posts',
                subcategory_ids: selectedCategories
            },
            success: function(response) {
                $('#custom-posts').html(response);
            },
            error: function() {
                $('#custom-posts').html('<p>خطا در بارگذاری پست‌ها.</p>');
            }
        });
    });

    // ارسال درخواست AJAX برای بارگذاری پست‌ها در بارگیری صفحه
    $('.custom-subcategory').first().change();
});
