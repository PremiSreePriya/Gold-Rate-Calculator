<?php
require_once 'db.php';

// Handle AJAX for category items
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] == 'get_items' && isset($_GET['category_id'])) {
        $category_id = (int)$_GET['category_id'];
        $result = $conn->query("SELECT id, item_name FROM items WHERE category_id = $category_id");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        echo json_encode($items);
        exit;
    }

    if ($_GET['action'] == 'get_item_details' && isset($_GET['item_id'])) {
        $item_id = (int)$_GET['item_id'];
        $result = $conn->query("SELECT weight, wastage_percent, making_percent, tax_percent, image_path FROM items WHERE id = $item_id");
        echo json_encode($result->fetch_assoc());
        exit;
    }
}

// ✅ Include image_path in category query
$categories_result = $conn->query("SELECT id, category_name, image_path FROM categories");
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Gold Price Calculator</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background-image: url('https://wallpapercave.com/wp/wp8149620.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-attachment: fixed;
      background-position: center;
    }

    h2 {
      text-align: center;
      color: #FFD700;
      margin-bottom: 30px;
    }

    .form-container {
      max-width: 800px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .selection-row {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      gap: 15px;
    }

    .selection-row label {
      min-width: 100px;
      font-weight: bold;
    }

    .selection-row select {
      padding: 8px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
    }

    .selection-row img {
      max-width: 80px;
      max-height: 80px;
      border-radius: 5px;
    }

    .calculator-section {
      border: 2px solid #007bff;
      border-radius: 10px;
      padding: 20px;
      margin-top: 20px;
    }

    .calc-row {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
      padding: 10px;
      background-color: #f8f9fa;
      border-radius: 5px;
    }

    .calc-row label {
      flex: 1;
      font-weight: bold;
    }

    .calc-row input {
      width: 100px;
      padding: 8px;
      border: 2px solid #ddd;
      border-radius: 5px;
      text-align: center;
      margin: 0 10px;
    }

    .amount-display {
      min-width: 120px;
      padding: 8px;
      background: #e9ecef;
      border: 2px solid #ced4da;
      border-radius: 5px;
      text-align: right;
      font-weight: bold;
    }

    .total-row {
      background: #d4edda;
      border: 2px solid #c3e6cb;
    }

    .total-row .amount-display {
      background: #c3e6cb;
      color: #155724;
    }

    .calculate-btn {
      display: block;
      width: 200px;
      margin: 20px auto;
      padding: 12px;
      background: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
    }

    .calculate-btn:hover {
      background: #0056b3;
    }

    .calculate-btn:disabled {
      background: #6c757d;
      cursor: not-allowed;
    }
  </style>
</head>
<body>
  <h2>Gold Price Calculator</h2>
  <div class="form-container">
    <form id="goldForm" onsubmit="return false;">
      <div class="selection-row">
        <label for="category">Category:</label>
        <select id="category">
          <option value="">Select Category</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <img id="category-img" hidden />
      </div>

      <div class="selection-row">
        <label for="item">Item:</label>
        <select id="item" disabled>
          <option value="">Select Item</option>
        </select>
        <img id="item-img" hidden />
      </div>

      <div class="calculator-section">
        <div class="calc-row"><label>Gold Rate:</label><input id="goldRate" readonly><div class="amount-display" id="goldRateAmt">₹0.00</div></div>
        <div class="calc-row"><label>Gold Weight 'g':</label><input id="weight" readonly><div class="amount-display" id="goldWtAmt">₹0.00</div></div>
        <div class="calc-row"><label>Making Charges %:</label><input id="making" readonly><div class="amount-display" id="makingChargeAmt">₹0.00</div></div>
        <div class="calc-row"><label>Wastage %:</label><input id="wastage" readonly><div class="amount-display" id="wastageAmt">₹0.00</div></div>
        <div class="calc-row"><label>Tax %:</label><input id="tax" readonly><div class="amount-display" id="taxAmt">₹0.00</div></div>
        <div class="calc-row total-row"><label>Total Price:</label><input readonly style="border:none;"><div class="amount-display" id="totalAmt">₹0.00</div></div>
      </div>

      <button type="button" id="calculateBtn" class="calculate-btn" disabled>Calculate Price</button>
    </form>
  </div>

<script>
const categories = <?= json_encode($categories); ?>;

document.addEventListener('DOMContentLoaded', () => {
  const category = document.getElementById('category');
  const item = document.getElementById('item');
  const goldRate = document.getElementById('goldRate');
  const weight = document.getElementById('weight');
  const making = document.getElementById('making');
  const wastage = document.getElementById('wastage');
  const tax = document.getElementById('tax');
  const calcBtn = document.getElementById('calculateBtn');
  const catImg = document.getElementById('category-img');
  const itemImg = document.getElementById('item-img');

  category.addEventListener('change', () => {
    const id = category.value;
    if (!id) return;

    // ✅ Set category image
    const selected = categories.find(c => c.id == id);
    if (selected && selected.image_path) {
      catImg.src = selected.image_path;
      catImg.hidden = false;
    } else {
      catImg.hidden = true;
    }

    fetch(`index.php?action=get_items&category_id=${id}`)
      .then(res => res.json())
      .then(data => {
        item.innerHTML = '<option value="">Select Item</option>';
        data.forEach(i => {
          const opt = document.createElement('option');
          opt.value = i.id;
          opt.textContent = i.item_name;
          item.appendChild(opt);
        });
        item.disabled = false;
      });
  });

  item.addEventListener('change', () => {
    const id = item.value;
    fetch(`index.php?action=get_item_details&item_id=${id}`)
      .then(res => res.json())
      .then(data => {
        weight.value = data.weight;
        wastage.value = data.wastage_percent;
        making.value = data.making_percent;
        tax.value = data.tax_percent;
        calcBtn.disabled = false;

        // ✅ Set item image
        if (data.image_path) {
          itemImg.src = data.image_path;
          itemImg.hidden = false;
        } else {
          itemImg.hidden = true;
        }
      });
  });

  calcBtn.addEventListener('click', () => {
    fetch('get_gold_data.php')
      .then(res => res.json())
      .then(data => {
        const rate = parseFloat(data.rate);
        const w = parseFloat(weight.value);
        const ws = parseFloat(wastage.value);
        const mk = parseFloat(making.value);
        const tx = parseFloat(tax.value);

        const base = w * rate;
        const waste = base * (ws / 100);
        const make = base * (mk / 100);
        const sub = base + waste + make;
        const taxAmt = sub * (tx / 100);
        const total = sub + taxAmt;

        goldRate.value = rate;
        document.getElementById('goldRateAmt').textContent = `₹${rate.toFixed(2)}`;
        document.getElementById('goldWtAmt').textContent = `₹${base.toFixed(2)}`;
        document.getElementById('makingChargeAmt').textContent = `₹${make.toFixed(2)}`;
        document.getElementById('wastageAmt').textContent = `₹${waste.toFixed(2)}`;
        document.getElementById('taxAmt').textContent = `₹${taxAmt.toFixed(2)}`;
        document.getElementById('totalAmt').textContent = `₹${total.toFixed(2)}`;
      });
  });
});
</script>
</body>
</html>
