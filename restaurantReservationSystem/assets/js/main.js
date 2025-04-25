document.addEventListener('DOMContentLoaded', function() {
    // Get references to all form elements and divs here
    const reservationForm = document.getElementById('reservationForm');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    const guestsSelect = document.getElementById('guests');
    const tableMapVisualization = document.getElementById('tableMapVisualization');
    const selectedTableInfo = document.getElementById('selectedTableInfo');
    const selectedTableIdInput = document.getElementById('selectedTableId');
    const selectedTableTypeInput = document.getElementById('selectedTableType'); // Make sure these exist in HTML if used
    const selectedTableLocationInput = document.getElementById('selectedTableLocation'); // Make sure these exist in HTML if used
    const selectedTablePreferenceInput = document.getElementById('selectedTablePreference'); // Make sure these exist in HTML if used
    const confirmButton = reservationForm.querySelector('button[type="submit"]');
    const reservationMessageDiv = document.getElementById('reservationMessage'); // Assuming you have this div in your form
    const reservationModalElement = document.getElementById('reservationModal'); // Your modal element
    // Check if modal element exists before creating Bootstrap modal instance
    const reservationModal = reservationModalElement ? new bootstrap.Modal(reservationModalElement) : null;


    // Disable the submit button initially
    confirmButton.disabled = true;

    // Add event listeners for date, time, guests to fetch available tables
    dateInput.addEventListener('change', fetchAvailableTables);
    timeSelect.addEventListener('change', fetchAvailableTables);
    guestsSelect.addEventListener('change', fetchAvailableTables);

    // Function to fetch and display available tables
    function fetchAvailableTables() {
        const selectedDate = dateInput.value;
        const selectedTime = timeSelect.value;
        const numberOfGuests = guestsSelect.value;

        // Only fetch if date, time, and guests are selected and guests count is valid
        if (selectedDate && selectedTime && numberOfGuests && numberOfGuests > 0) {
            // Show a loading indicator while fetching
            tableMapVisualization.innerHTML = '<p>Loading available tables...</p>';
            selectedTableInfo.textContent = ''; // Clear previous selected table info

            // Clear hidden inputs when criteria changes (forces user to re-select table)
            selectedTableIdInput.value = '';
            if (selectedTableTypeInput) selectedTableTypeInput.value = '';
            if (selectedTableLocationInput) selectedTableLocationInput.value = '';
            if (selectedTablePreferenceInput) selectedTablePreferenceInput.value = '';


            // Disable the submit button while loading/selecting
            confirmButton.disabled = true;

            // Use Fetch API to send data to the server (get_available_tables.php)
            fetch('get_available_tables.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', // Standard form submission type
                },
                // Send the selected criteria in the body
                body: new URLSearchParams({
                    date: selectedDate,
                    time: selectedTime,
                    guests: numberOfGuests
                })
            })
            .then(response => {
                if (!response.ok) {
                    // If not successful, throw an error
                    return response.json().catch(() => {
                         throw new Error('HTTP error ' + response.status + ': ' + response.statusText);
                    });
                }
                // Parse the JSON response from get_available_tables.php
                return response.json();
            })
            .then(data => {
                // Clear the visualization area before rendering new tables
                tableMapVisualization.innerHTML = '';

                if (data.success) {
                    if (data.tables && data.tables.length > 0) {
                         // Add a prompt to select a table
                        tableMapVisualization.innerHTML = '<p>Select an available table:</p>';

                        // Render the available tables received from the PHP script
                        data.tables.forEach(table => {
                            // Create a div element to represent each table
                            const tableElement = document.createElement('div');
                            tableElement.classList.add('table-visualization-item');

                            // Apply basic inline styles (better to use CSS classes)
                            tableElement.style.width = '60px';
                            tableElement.style.height = '60px';
                            tableElement.style.backgroundColor = '#28a745'; // Green for available
                            tableElement.style.borderRadius = '8px';
                            tableElement.style.margin = '8px';
                            tableElement.style.display = 'inline-flex';
                            tableElement.style.justifyContent = 'center';
                            tableElement.style.alignItems = 'center';
                            tableElement.style.color = 'white';
                            tableElement.style.fontWeight = 'bold';
                            tableElement.style.cursor = 'pointer';
                            tableElement.style.border = '3px solid transparent';
                            tableElement.style.transition = 'all 0.2s ease-in-out';
                            tableElement.style.boxShadow = '2px 2px 5px rgba(0, 0, 0, 0.1)';
                            tableElement.style.fontSize = '0.9em';


                            // Display table capacity or ID
                            tableElement.textContent = table.capacity + ' guests';


                            // Store table data in data attributes
                            tableElement.dataset.tableId = table.id; // Physical table ID
                            tableElement.dataset.tableCapacity = table.capacity;
                            tableElement.dataset.tableLocation = table.location || ''; // Use || '' for safety
                            tableElement.dataset.tableType = table.table_type || '';
                            tableElement.dataset.tablePreference = table.table_preference || '';


                            // Add a click event listener to handle selecting this table
                            tableElement.addEventListener('click', handleTableSelection);

                            // Append the table element
                            tableMapVisualization.appendChild(tableElement);
                        });

                    } else {
                        // If no tables are available
                        tableMapVisualization.innerHTML = '<p>No tables available for this time slot and guest count.</p>';
                         confirmButton.disabled = true;
                    }
                } else {
                    // Handle errors from get_available_tables.php
                    tableMapVisualization.innerHTML = '<p class="text-danger">Error fetching tables: ' + data.message + '</p>';
                     confirmButton.disabled = true;
                }
            })
            .catch(error => {
                // Handle network errors or issues with fetch to get_available_tables.php
                console.error('Error fetching available tables:', error);
                tableMapVisualization.innerHTML = '<p class="text-danger">Could not load tables. Please check your connection or try again.</p>';
                 confirmButton.disabled = true;
            });
        } else {
             // If date, time, or guests are not fully selected
             tableMapVisualization.innerHTML = '<p class="text-muted">Select Date, Time, and Guests to see available tables.</p>';
             selectedTableInfo.textContent = '';
             selectedTableIdInput.value = '';
             if (selectedTableTypeInput) selectedTableTypeInput.value = '';
             if (selectedTableLocationInput) selectedTableLocationInput.value = '';
             if (selectedTablePreferenceInput) selectedTablePreferenceInput.value = '';
             confirmButton.disabled = true;
        }
    }

    // Function to handle when a table visualization element is clicked
    function handleTableSelection(event) {
        // Remove selected class from others
        document.querySelectorAll('.table-visualization-item').forEach(item => {
            item.classList.remove('selected');
             item.style.border = '3px solid transparent';
        });

        // Add selected class to clicked item
        const selectedTableElement = event.target;
        selectedTableElement.classList.add('selected');
         selectedTableElement.style.border = '3px solid #007bff';


        // Get table data from data attributes
        const tableId = selectedTableElement.dataset.tableId;
        const tableType = selectedTableElement.dataset.tableType;
        const tableLocation = selectedTableElement.dataset.tableLocation;
        const tablePreference = selectedTableElement.dataset.tablePreference;
        const tableCapacity = selectedTableElement.dataset.tableCapacity;


        // Populate hidden inputs
        selectedTableIdInput.value = tableId;
        if (selectedTableTypeInput) selectedTableTypeInput.value = tableType;
        if (selectedTableLocationInput) selectedTableLocationInput.value = tableLocation;
        if (selectedTablePreferenceInput) selectedTablePreferenceInput.value = tablePreference;


        // Display selected table info
        selectedTableInfo.textContent = `Selected Table: ID ${tableId} (${tableCapacity} guests)`;
         if(tableLocation) selectedTableInfo.textContent += `, Location: ${tableLocation}`;


        // Enable the submit button
        confirmButton.disabled = false;
    }

    
}); // End of DOMContentLoaded listener