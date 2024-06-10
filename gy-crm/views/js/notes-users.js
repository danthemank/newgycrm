
jQuery(document).ready(() => {
    // first Load of commet
    jQuery.ajax({
        type: "POST",
        url: "/wp-admin/admin-ajax.php",
        data: {
            action: "get_notes",
            'user_id': getUserID(),
        },
        success: function (response) {
            jQuery('.absolute').hide();
            let data = JSON.parse(response)
            inserComment(data)
        }
    });
});

// save and update the commet
jQuery('.add_note_button').on('click', function () {
    jQuery('#order-notes .absolute').show();
    let content = jQuery("#content_note").val()
    let normal = jQuery("#order_note_type").val()
    if (content) {
        jQuery.ajax({
            type: "POST",
            url: "/wp-admin/admin-ajax.php",
            data: {
                action: "get_notes",
                'save': "yes",
                'content': content,
                'user_id': getUserID(),
                'normal': normal,
            },
            success: function (response) {
                jQuery('.absolute').hide();
                let data = JSON.parse(response)
                inserComment(data)
                jQuery("#content_note").val("")
            }
        });
    }
});

// delete a commet
function deleteComment(commentID) {
    jQuery('.absolute').show();
    jQuery.ajax({
        type: "POST",
        url: "/wp-admin/admin-ajax.php",
        data: {
            action: "get_notes",
            'user_id': getUserID(),
            'delete': commentID,
        },
        success: function (response) {
            jQuery('.absolute').hide();
            let data = JSON.parse(response)
            inserComment(data)
        }
    });
}

function inserComment(data) {
    let isCapable = jQuery('.customer-information-container #is_edit_capable').val()
    let container = jQuery(".order_notes")
    container.html("")
    data.forEach(comment => {
        let date = new Date(comment["comment_date"]).toLocaleDateString('en-us', { weekday: "long", year: "numeric", month: "short", day: "numeric" });
        let singleComment = `<li rel="${comment["comment_ID"]}" class="note ${comment["meta_value"] == "1" ? "customer-note" :""}">
                            <div class="note_content">
                                <p>${comment["comment_content"]}</p>
                            </div>
                            <div class="meta flex-container">
                                <div>
                                    <abbr class="exact-date" title="23-05-30 08:21:22">
                                        added on ${date} </abbr>
                                    <span>by ${comment["comment_author"]}</span>
                                </div>`
        if (isCapable !== 'false') {
            singleComment += `<a style="cursor:pointer;" onclick="deleteComment(${comment["comment_ID"]})"class="delete_note" role="button">Delete note</a>`
        }
                singleComment += `</div>
            </li>`
        container.append(singleComment)
    });
}

function getUserID(){
    let url_string = window.location.href
    var url = new URL(url_string);
    var userID = url.searchParams.get("user");
    return userID
}
