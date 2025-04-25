document.addEventListener('DOMContentLoaded', function() {
    // Event delegation for delete forms in included content
    document.body.addEventListener('submit', function(event) {
        if (event.target && (event.target.classList.contains('delete-reservation-form') || event.target.classList.contains('delete-table-form'))) {
            const confirmation = confirm('Are you sure you want to delete this item? This action cannot be undone.');
            if (!confirmation) {
                event.preventDefault(); // Prevent form submission if user cancels
            }
        }
    });

     // Basic JavaScript for search/filter in the reservations list
     // Assumes the HTML structure with IDs/classes from manage_reservations.php is loaded
     const searchInput = document.getElementById('searchInput');
     const filterSelect = document.getElementById('filterSelect');
     const reservationList = document.querySelector('.reservation-list'); // Assuming this class exists in manage_reservations.php

     if (searchInput && filterSelect && reservationList) {
         // Re-select cards as the list might be loaded via include
         const cards = reservationList.querySelectorAll('.reservation-card');

         function filterAndSearch() {
             const searchTerm = searchInput.value.toLowerCase();
             const statusFilter = filterSelect.value;

             cards.forEach(card => {
                 // Ensure data attributes are lowercase for consistent matching
                 const cardStatus = card.dataset.status ? card.dataset.status.toLowerCase() : '';
                 const searchTerms = card.dataset.searchTerms ? card.dataset.searchTerms.toLowerCase() : '';

                 const statusMatch = statusFilter === 'all' || cardStatus === statusFilter;
                 const searchMatch = searchTerms.includes(searchTerm);

                 if (statusMatch && searchMatch) {
                     card.style.display = ''; // Show card
                 } else {
                     card.style.display = 'none'; // Hide card
                 }
             });
         }

         searchInput.addEventListener('input', filterAndSearch);
         filterSelect.addEventListener('change', filterAndSearch);

         // Initial filter/search on load
         filterAndSearch();
     }

      // Simple refresh button (if included in manage_reservations.php or manage_tables.php)
      // Ensure the button has the ID 'refreshBtn'
     const refreshBtn = document.getElementById('refreshBtn');
     if (refreshBtn) {
         refreshBtn.addEventListener('click', function() {
             // Reload the current page, preserving the 'page' parameter
             const currentUrl = new URL(window.location.href);
             // Remove status and message parameters before reloading to avoid reappearing
             currentUrl.searchParams.delete('status');
             currentUrl.searchParams.delete('message');
             window.location.href = currentUrl.toString();
         });
     }


});

 // JavaScript for clearing URL parameters after message display
 // This will clean up the URL after a redirect with status/message parameters
 window.addEventListener('load', function() {
      const url = new URL(window.location.href);
      if (url.searchParams.has('status') || url.searchParams.has('message')) {
           url.searchParams.delete('status');
           url.searchParams.delete('message');
           if (window.history.replaceState) {
                window.history.replaceState({}, document.title, url.toString());
           }
      }

      // Inside the document.addEventListener('DOMContentLoaded', function() { ... }); in admin.php

         // ... (other event listeners) ...

         // Event delegation for delete user forms
         document.body.addEventListener('submit', function(event) {
            if (event.target && event.target.classList.contains('delete-user-form')) {
                const confirmation = confirm('Are you sure you want to delete this user? This action cannot be undone.');
                if (!confirmation) {
                    event.preventDefault(); // Prevent form submission if user cancels
                }
            }
        });

        // ... (rest of your JavaScript) ...
 });
