function filterProducts() {
  var filter = document.getElementById("filter-select").value;

  var productCards = document.querySelectorAll(".product-card");

  for (var i = 0; i < productCards.length; i++) {
    var card = productCards[i];

    console.log(card.dataset.isman)
    if (filter === "") {
      card.style.display = "block";
    } else if (filter === "man" && card.dataset.isman === "man") {
      card.style.display = "block";
    } else if (filter === "notman" && card.dataset.isman === "notman") {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  }
}

function sortCart(sort) {
  const cartContainer = document.getElementById("cartModal");
  const productCards = cartContainer.getElementsByClassName("product-card");

  const productArray = Array.from(productCards);

  productArray.sort((a, b) => {
    const aPrice = parseInt(a.getAttribute("data-price"));
    const bPrice = parseInt(b.getAttribute("data-price"));

    const aQuantity = parseInt(a.getAttribute("data-quantity"));
    const bQuantity = parseInt(b.getAttribute("data-quantity"));

    switch (sort) {
      case "name-asc":
        return a.querySelector(".card-title").textContent.localeCompare(
          b.querySelector(".card-title").textContent
        );
      case "name-desc":
        return b.querySelector(".card-title").textContent.localeCompare(
          a.querySelector(".card-title").textContent
        );
      case "price-asc":
        return aPrice - bPrice;
      case "price-desc":
        return bPrice - aPrice;
      case "total-asc":
        return aPrice * aQuantity - bPrice * bQuantity;
      case "total-desc":
        return bPrice * bQuantity - aPrice * aQuantity;
      case "prikol-asc":
        return parseInt(a.getAttribute("data-prikol")) - parseInt(b.getAttribute("data-prikol"));
      case "prikol-desc":
        return parseInt(b.getAttribute("data-prikol")) - parseInt(a.getAttribute("data-prikol"));
    }
  });

  while (productCards.length > 0) {
    productCards[0].remove();
  }

  for (const card of productArray) {
    cartContainer.appendChild(card);
  }
}

const sortSelect = document.getElementById("sort-select");
sortSelect.addEventListener("change", function () {
  const selectedSort = sortSelect.value;
  sortCart(selectedSort);
});

const initialSort = "<?php echo $sort; ?>";
if (initialSort) {
  sortSelect.value = initialSort;
  sortCart(initialSort);
}
