// app.js

// Simulated "database"
let properties = [];
let currentEditIndex = null;

// DOM Elements
const propertyForm = document.getElementById('propertyForm');
const propertyId = document.getElementById('propertyId');
const itemName = document.getElementById('itemName');
const price = document.getElementById('price');
const contact = document.getElementById('contact');
const propertyTableBody = document.querySelector('#propertyTable tbody');

// Function to render the property list
async function renderProperties() {
  try {
    const response = await fetch('http://localhost:8000/properties');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    properties = data;
    console.log('Fetched properties:', properties);
    
    propertyTableBody.innerHTML = ''; // Clear existing rows
    
    properties.forEach((property, index) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${property.itemName}</td>
        <td>$${property.price}</td>
        <td>${property.contact}</td>
        <td class="actions">
          <button class="edit" onclick="editProperty(${index})">Edit</button>
          <button class="delete" onclick="deleteProperty(${property.id})">Delete</button>
        </td>
      `;
      propertyTableBody.appendChild(row);
    });
  } catch (error) {
    console.error('Error fetching properties:', error);
    propertyTableBody.innerHTML = '<tr><td colspan="4">Error loading properties</td></tr>';
  }
}

// Add or Update Property
propertyForm.addEventListener('submit', async function (e) {
  e.preventDefault();

  try {
    const itemData = {
      itemName: itemName.value,
      price: parseFloat(price.value),
      contact: contact.value
    };

    let response;
    if (currentEditIndex !== null) {
      // Update existing property
      const id = properties[currentEditIndex].id;
      response = await fetch(`http://localhost:8000/properties/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(itemData)
      });
    } else {
      // Add new property
      response = await fetch('http://localhost:8000/properties', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(itemData)
      });
    }

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const savedItem = await response.json();
    
    if (currentEditIndex !== null) {
      // Update existing property
      properties[currentEditIndex] = savedItem;
      currentEditIndex = null;
    } else {
      // Add new property
      properties.push(savedItem);
    }

    // Clear form
    propertyForm.reset();
    propertyId.value = '';

    // Re-render the list
    renderProperties();
  } catch (error) {
    console.error('Error saving property:', error);
    alert('Error saving property. Please try again.');
  }
});

// Edit Property
function editProperty(index) {
  const property = properties[index];
  currentEditIndex = index;
  propertyId.value = property.id;
  itemName.value = property.itemName;
  price.value = property.price;
  contact.value = property.contact;
}

// Delete Property
async function deleteProperty(id) {
  if (!confirm('Are you sure you want to delete this property?')) {
    return;
  }

  try {
    const response = await fetch(`http://localhost:8000/properties/${id}`, {
      method: 'DELETE'
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    // Re-render the list
    renderProperties();
  } catch (error) {
    console.error('Error deleting property:', error);
    alert('Error deleting property. Please try again.');
  }
}

// Initial Render
window.addEventListener('load', renderProperties);