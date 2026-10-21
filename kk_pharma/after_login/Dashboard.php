<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pharma User Dashboard – Cart & Wishlist</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#f4f6f9}
.section{display:none}
.section.active{display:block}
.card{border-radius:12px}
.price{font-size:1.2rem;font-weight:600;color:#0d6efd}
.old-price{text-decoration:line-through;color:#888;font-size:0.9rem}
.wishlist{cursor:pointer;color:#dc3545}
.error{color:red;font-size:0.85rem;display:none}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand">PharmaCare</a>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link" onclick="showSection('products')">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" onclick="showSection('cart')">Cart (<span id="cartCount">0</span>)</a></li>
      <li class="nav-item"><a class="nav-link" onclick="showSection('wishlist')">Wishlist (<span id="wishCount">0</span>)</a></li>
      <li class="nav-item"><a class="nav-link" onclick="showSection('profile')">Profile</a></li>
      <li class="nav-item"><a class="nav-link" onclick="showSection('password')">Change Password</a></li>
      <li class="nav-item"><a class="nav-link" onclick="showSection('feedback')">Feedback</a></li>
      <li class="nav-item"><a class="nav-link text-danger">Logout</a></li>
    </ul>
  </div>
</nav>

<div class="container mt-4">

<!-- PRODUCT DASHBOARD -->
<div id="products" class="section active">
<h4 class="mb-3">Popular Medicines</h4>
<div class="row g-4">

<div class="col-md-3">
<div class="card h-100">
<img src="https://via.placeholder.com/200x150" class="card-img-top">
<div class="card-body">
<h6>Paracetamol 500mg</h6>
<p class="text-muted">Strip of 10 tablets</p>
<p><span class="price">₹25</span> <span class="old-price">₹30</span></p>
<button class="btn btn-success btn-sm w-100" onclick="addToCart('Paracetamol 500mg',25)">Add to Cart</button>
<div class="wishlist text-center mt-2" onclick="addToWishlist('Paracetamol 500mg',25)"><i class="bi bi-heart"></i> Wishlist</div>
</div></div></div>

<div class="col-md-3">
<div class="card h-100">
<img src="https://via.placeholder.com/200x150" class="card-img-top">
<div class="card-body">
<h6>Azithromycin 250mg</h6>
<p class="text-muted">Strip of 6 tablets</p>
<p><span class="price">₹85</span> <span class="old-price">₹99</span></p>
<button class="btn btn-success btn-sm w-100" onclick="addToCart('Azithromycin 250mg',85)">Add to Cart</button>
<div class="wishlist text-center mt-2" onclick="addToWishlist('Azithromycin 250mg',85)"><i class="bi bi-heart"></i> Wishlist</div>
</div></div></div>

<div class="col-md-3">
<div class="card h-100">
<img src="https://via.placeholder.com/200x150" class="card-img-top">
<div class="card-body">
<h6>Vitamin C Tablets</h6>
<p class="text-muted">Bottle of 60 tablets</p>
<p><span class="price">₹210</span> <span class="old-price">₹250</span></p>
<button class="btn btn-success btn-sm w-100" onclick="addToCart('Vitamin C Tablets',210)">Add to Cart</button>
<div class="wishlist text-center mt-2" onclick="addToWishlist('Vitamin C Tablets',210)"><i class="bi bi-heart"></i> Wishlist</div>
</div></div></div>

<div class="col-md-3">
<div class="card h-100">
<img src="https://via.placeholder.com/200x150" class="card-img-top">
<div class="card-body">
<h6>Cough Syrup</h6>
<p class="text-muted">Bottle of 100ml</p>
<p><span class="price">₹120</span> <span class="old-price">₹150</span></p>
<button class="btn btn-success btn-sm w-100" onclick="addToCart('Cough Syrup',120)">Add to Cart</button>
<div class="wishlist text-center mt-2" onclick="addToWishlist('Cough Syrup',120)"><i class="bi bi-heart"></i> Wishlist</div>
</div></div></div>

</div>
</div>

<!-- CART PAGE -->
<div id="cart" class="section">
<h4>Your Cart</h4>
<table class="table table-bordered">
<thead class="table-light">
<tr><th>Medicine</th><th>Price</th><th>Qty</th><th>Total</th><th>Action</th></tr>
</thead>
<tbody id="cartItems"></tbody>
</table>
<h5 class="text-end">Grand Total: ₹<span id="grandTotal">0</span></h5>
</div>

<!-- WISHLIST PAGE -->
<div id="wishlist" class="section">
<h4>Your Wishlist</h4>
<table class="table table-bordered">
<thead class="table-light">
<tr><th>Medicine</th><th>Price</th><th>Action</th></tr>
</thead>
<tbody id="wishItems"></tbody>
</table>
</div>

<!-- PROFILE -->
<div id="profile" class="section">
<h4>Profile</h4>
<form onsubmit="return validateProfile()">
<div class="mb-3"><label>Name</label><input id="name" class="form-control"><div id="nameErr" class="error">Required</div></div>
<div class="mb-3"><label>Email</label><input id="email" class="form-control"><div id="emailErr" class="error">Invalid Email</div></div>
<div class="mb-3"><label>Mobile</label><input id="mobile" class="form-control"><div id="mobileErr" class="error">10 digit required</div></div>
<button class="btn btn-primary">Update</button>
</form>
</div>

<!-- PASSWORD -->
<div id="password" class="section">
<h4>Change Password</h4>
<form onsubmit="return validatePassword()">
<div class="mb-3"><label>New Password</label><input type="password" id="newpass" class="form-control"><div id="passErr" class="error">Min 6 chars</div></div>
<div class="mb-3"><label>Confirm Password</label><input type="password" id="confirmpass" class="form-control"><div id="confirmErr" class="error">Mismatch</div></div>
<button class="btn btn-success">Change</button>
</form>
</div>

<!-- FEEDBACK -->
<div id="feedback" class="section">
<h4>Feedback</h4>
<form onsubmit="return validateFeedback()">
<div class="mb-3"><label>Message</label><textarea id="message" class="form-control"></textarea><div id="msgErr" class="error">Required</div></div>
<button class="btn btn-warning">Submit</button>
</form>
</div>

</div>

<script>
let cart=[],wishlist=[];
function showSection(id){document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));document.getElementById(id).classList.add('active');}

function addToCart(name,price){let i=cart.find(p=>p.name===name);if(i){i.qty++;}else{cart.push({name,price,qty:1});}updateCart();}

function updateCart(){let t=document.getElementById('cartItems');t.innerHTML='';let total=0,count=0;cart.forEach((i,idx)=>{t.innerHTML+=`<tr><td>${i.name}</td><td>₹${i.price}</td><td>${i.qty}</td><td>₹${i.price*i.qty}</td><td><button class='btn btn-sm btn-danger' onclick='removeCart(${idx})'>Remove</button></td></tr>`;total+=i.price*i.qty;count+=i.qty;});grandTotal.innerText=total;cartCount.innerText=count;}

function removeCart(i){cart.splice(i,1);updateCart();}

function addToWishlist(name,price){if(!wishlist.find(w=>w.name===name)){wishlist.push({name,price});updateWishlist();}}

function updateWishlist(){let t=document.getElementById('wishItems');t.innerHTML='';wishlist.forEach((w,idx)=>{t.innerHTML+=`<tr><td>${w.name}</td><td>₹${w.price}</td><td><button class='btn btn-sm btn-success' onclick="addToCart('${w.name}',${w.price})">Add to Cart</button> <button class='btn btn-sm btn-danger' onclick='removeWish(${idx})'>Remove</button></td></tr>`;});wishCount.innerText=wishlist.length;}

function removeWish(i){wishlist.splice(i,1);updateWishlist();}

function validateProfile(){let v=true;name.value.trim()?nameErr.style.display='none':(nameErr.style.display='block',v=false);/^\S+@\S+\.\S+$/.test(email.value)?emailErr.style.display='none':(emailErr.style.display='block',v=false);/^[0-9]{10}$/.test(mobile.value)?mobileErr.style.display='none':(mobileErr.style.display='block',v=false);return v;}
function validatePassword(){passErr.style.display=newpass.value.length>=6?'none':'block';confirmErr.style.display=newpass.value===confirmpass.value?'none':'block';return newpass.value.length>=6 && newpass.value===confirmpass.value;}
function validateFeedback(){msgErr.style.display=message.value.trim()?'none':'block';return message.value.trim().length>0;}
</script>
</body>
</html>