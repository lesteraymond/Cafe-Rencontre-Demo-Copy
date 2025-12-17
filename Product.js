    let editingProduct = null;

    document.querySelector('.products a').classList.add('active');
    document.getElementById("popup").style.display = "none";
    document.getElementById("deletePopup").style.display = "none";
    document.getElementById("pprice").addEventListener("input", function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById("add-button").onclick = () => {
        editingProduct = null;
        clearFields();
        document.querySelector(".popup-content h2").textContent = "Add Product";
        document.getElementById("confirmPopup").textContent = "Confirm";
        document.getElementById("popup").style.display = "flex";
    };

    document.getElementById("closePopup").onclick = closePopup;
    document.getElementById("cancelPopup").onclick = closePopup;

    function closePopup() {
        document.getElementById("popup").style.display = "none";
    }

    function clearFields() {
        document.getElementById("pname").value = "";
        document.getElementById("pcategory").value = "";
        document.getElementById("pdesc").value = "";
        document.getElementById("pprice").value = "";
    }
    function closeDeletePopup() {
        document.getElementById("deletePopup").style.display = "none";
    }

    document.getElementById("closeDeletePopup").onclick = closeDeletePopup;
    document.getElementById("cancelDelete").onclick = closeDeletePopup;

    document.getElementById("confirmPopup").onclick = () => {
        let name = document.getElementById("pname").value;
        let cat = document.getElementById("pcategory").value;
        let desc = document.getElementById("pdesc").value;
        let price = document.getElementById("pprice").value;

        if (!name || !cat || !desc || !price) {
            alert("Fill all fields");
            return;
        }

        if (editingProduct) {
            editingProduct.querySelector(".name h3").textContent = name;
            editingProduct.querySelector(".category p").textContent = cat;
            editingProduct.querySelector(".description p").textContent = desc;
            editingProduct.querySelector(".price-stock p").textContent = `₱${price}`;
            editingProduct = null;
            closePopup();
            return;
        }

        let container = document.getElementById("productList");

        let product = document.createElement("div");
        product.className = "product-item";

        product.innerHTML = `
            <div class="product-info">
              <div class="name-category">
                <div class="name">
                  <h3>${name}</h3>
                </div>

                <div class="category">
                  <p>${cat}</p>
                </div>
              </div>

              <div class="description">
                <p><span class="desc-text">${desc}</span> <img class="edit-icon" src="/Background-Image/edit icon.png"></p>
              </div>
            </div>

            <div class="price-stock-button-delete-button">
                <div class="price-stock">
                    <p>₱${price}</p>
                </div>

                <div class="button-group">
                    <button class="delete-btn">
                      <img src="/Background-Image/delete icon.png">
                    </button>
                    <button class="stock-toggle">
                      <img class="in-stock" src="/Background-Image/toggle left.jpg">
                    </button>
                </div>
            </div>
        `;

        product.querySelector(".edit-icon").onclick = () => openEdit(product);

     product.querySelector(".delete-btn").onclick = () => openDeleteConfirmation(product);

        const stockToggle = product.querySelector(".stock-toggle");
        const stockImage = stockToggle.querySelector(".in-stock");

        stockToggle.onclick = () => {
            const currentSrc = stockImage.src.toLowerCase();
            
            if (currentSrc.includes("toggle left")) {
                stockImage.src = "/Background-Image/toggle right.jpg";
            } else {
                stockImage.src = "/Background-Image/toggle left.jpg";
            }
        };

        container.appendChild(product);
        closePopup();
    };

    function openEdit(product) {
        editingProduct = product;

        document.querySelector(".popup-content h2").textContent = "Edit Product";
        document.getElementById("confirmPopup").textContent = "Save";

        document.getElementById("pname").value =
            product.querySelector(".name h3").textContent;
        document.getElementById("pcategory").value =
            product.querySelector(".category p").textContent;
        document.getElementById("pdesc").value =
            product.querySelector(".description .desc-text").textContent;

        document.getElementById("pprice").value =
            product.querySelector(".price-stock p").textContent.replace("₱", "");

        document.getElementById("popup").style.display = "flex";
    }
    function openDeleteConfirmation(product) {
        const productName = product.querySelector(".name h3").textContent;
        document.getElementById("deleteProductName").textContent = productName;
        document.getElementById("deletePopup").style.display = "flex";
        document.getElementById("confirmDelete").onclick = () => {
            product.remove();
            closeDeletePopup();
        };
    }
