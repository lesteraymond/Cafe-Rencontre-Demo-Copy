let editingIngredient = null;
let deletingIngredient = null;

document.querySelector('.Ingredients a').classList.add('active');
document.getElementById("popup").style.display = "none";
document.getElementById("deletePopup").style.display = "none";
document.getElementById("quantity").addEventListener("input", function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
document.querySelector(".search-bar").addEventListener("input", function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const ingredientItems = document.querySelectorAll(".ingredient-item");

    ingredientItems.forEach(item => {
        const ingredientName = item.querySelector(".ingredient-name").textContent.toLowerCase();
        item.style.display = ingredientName.includes(searchTerm) ? "block" : "none";
    });
});

document.getElementById("add-button").onclick = () => {
  editingIngredient = null;
  clearFields();
  document.querySelector(".popup-content h2").textContent = "Add Ingredients";
  document.getElementById("confirmPopup").textContent = "Confirm";
  document.getElementById("popup").style.display = "flex";
};


document.getElementById("closePopup").onclick = closePopup;
document.getElementById("cancelPopup").onclick = closePopup;

function closePopup() {
  document.getElementById("popup").style.display = "none";
}


document.getElementById("closeDeletePopup").onclick = closeDeletePopup;
document.getElementById("cancelDelete").onclick = closeDeletePopup;

function closeDeletePopup() {
  document.getElementById("deletePopup").style.display = "none";
  deletingIngredient = null;
}

function clearFields() {
  document.getElementById("ingname").value = "";
  document.getElementById("unit").value = "";
  document.getElementById("pack").value = "";
  document.getElementById("quantity").value = "";
}

document.getElementById("confirmPopup").onclick = () => {
  const name = document.getElementById("ingname").value;
  const unit = document.getElementById("unit").value;
  const pack = document.getElementById("pack").value;
  const quantity = document.getElementById("quantity").value;

  if (!name || !unit || !pack || !quantity) {
      alert("Please fill all fields");
      return;
  }

  if (editingIngredient) {
      editingIngredient.querySelector(".ingredient-name").textContent = name;
      editingIngredient.querySelector(".bottles-count").textContent = pack;
      editingIngredient.querySelector(".available-quantity").textContent = quantity;
      editingIngredient.querySelector(".available-unit").textContent = unit;
    
      const percentage = calculatePercentage(pack, quantity);
      editingIngredient.querySelector(".progress-fill").style.width = percentage + "%";
      editingIngredient.querySelector(".percentage-text").textContent = Math.round(percentage) + "%";
      
      editingIngredient = null;
      closePopup();
      return;
  }
  addIngredient(name, unit, pack, quantity);
  closePopup();
};

document.getElementById("confirmDelete").onclick = () => {
  if (deletingIngredient) {
    deletingIngredient.remove();
    closeDeletePopup();
  }
};

function calculatePercentage(bottles, available) {
    const totalCapacity = bottles * 2000;
    const percentage = Math.min(100, (available / totalCapacity) * 100);
    return percentage;
}

function addIngredient(name, unit, bottles, available) {
    const container = document.getElementById("IngredientsList");
    const ingredient = document.createElement("div");
    ingredient.className = "ingredient-item";

    const percentage = calculatePercentage(bottles, available);

    ingredient.innerHTML = `
        <div class="ingredient-header">
            <h3 class="ingredient-name">${name}</h3>
            <div class="ingredient-actions">
                <button class="edit-btn-ing">
                    <img src="/Background-Image/edit icon.png" alt="Edit">
                </button>
                <button class="delete-btn-ing">
                    <img src="/Background-Image/trash icon.png" alt="Delete">
                </button>
            </div>
        </div>
        
        <div class="ingredient-info">
            <div class="bottles-info">
                <span class="label">Bottles:</span>
                <span class="bottles-count">${bottles}</span>
            </div>
            <div class="available-info">
                <span class="label">Available:</span>
                <span class="available-quantity">${available}</span>
                <span class="available-unit">${unit}</span>
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${percentage}%"></div>
            </div>
            <span class="percentage-text">${Math.round(percentage)}%</span>
        </div>
    `;

    ingredient.querySelector(".edit-btn-ing").onclick = () => openEdit(ingredient);

    ingredient.querySelector(".delete-btn-ing").onclick = () => openDeleteConfirmation(ingredient);

    container.appendChild(ingredient);
}

function openEdit(ingredient) {
    editingIngredient = ingredient;

    document.querySelector(".popup-content h2").textContent = "Edit Ingredients";
    document.getElementById("confirmPopup").textContent = "Save";

    const name = ingredient.querySelector(".ingredient-name").textContent;
    const bottles = ingredient.querySelector(".bottles-count").textContent;
    const quantity = ingredient.querySelector(".available-quantity").textContent;
    const unit = ingredient.querySelector(".available-unit").textContent;

    document.getElementById("ingname").value = name;
    document.getElementById("unit").value = unit;
    document.getElementById("pack").value = bottles;
    document.getElementById("quantity").value = quantity;

    document.getElementById("popup").style.display = "flex";
}


function openDeleteConfirmation(ingredient) {
    deletingIngredient = ingredient;
    document.getElementById("deletePopup").style.display = "flex";
}