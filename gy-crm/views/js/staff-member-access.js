(function () {
  document.addEventListener("DOMContentLoaded", function () {
    // Function to remove an element from the DOM
    function removeElement(selector) {
      var element = document.querySelector(selector);
      if (element) {
        element.remove();
      }
    }

    // List of CSS selectors for the elements you want to remove
    var selectorsToRemove = [
      ".wp-menu-separator",
      "#toplevel_page_kinsta-tools",
      "#menu-comments",
      "#toplevel_page_elementor",
      "#menu-posts-elementor_library",
      "#menu-users",
      "#menu-tools",
      "#menu-settings > ul > li:not(:last-child)",
      "#wp-admin-bar-comments",
      "#wp-admin-bar-new-elementor_library",
      "#wp-admin-bar-kinsta-cache",
      ".toplevel_page_woocommerce",
      ".toplevel_page_rank-math",
      "#menu-posts-shop_subscription",
      "#menu-posts",
      "#wp-admin-bar-new-content",
      "#wp-admin-bar-wp-logo",
      "#menu-posts-community_profile",
      "#menu-posts-gymnast-profile",
      "#thumbler-menu",
    ];

    // Loop through the list of selectors and remove the elements
    selectorsToRemove.forEach(function (selector) {
      removeElement(selector);
    });
  });
})();