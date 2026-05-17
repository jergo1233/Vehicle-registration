//ang file na ito ay para sa account_page.php 
// ang DASHBOARD at VEHICLE ay may sariling script at 
   
   
   
   document.addEventListener('DOMContentLoaded', (event) => {
    const editButtons = document.querySelectorAll('.editBtn');
    const popup = document.getElementById('popupForm');
    const closeBtn2 = document.getElementById('editBtn2'); 

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_fullname').value = this.dataset.fullname;
            document.getElementById('edit_username').value = this.dataset.username;
            document.getElementById('edit_email').value = this.dataset.email;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_confirm_password').value = '';
            document.getElementById('error_edit').textContent = '';

            // Ipakita ang form
            if (popup) {
                popup.style.display = 'block';
            }
        });
    });
    
    if (closeBtn2) { 
        closeBtn2.onclick = () => popup.style.display = 'none'; 
    }

    window.confirmDelete = function() {
        return confirm("Are you sure you want to delete this user?");
    }
    window.confirmLogout = function() {
        return confirm("Logout your account?");
    }

    // pass valditaio ADD USER
    window.checkPassword = function() {
        const p1 = document.getElementById('password').value;
        const p2 = document.getElementById('confirm_password').value;
        const err = document.getElementById('error');

        if (p1 !== p2) {
            err.style.color = 'red';
            err.textContent = 'Passwords do not match!';
            return false;
        }
        err.textContent = '';
        return true;
    }

    // pass validation EDIT (Optional Password)
    window.checkEditPassword = function() {
        const p1 = document.getElementById('edit_password').value;
        const p2 = document.getElementById('edit_confirm_password').value;
        const err = document.getElementById('error_edit');

        // Check kung nag-iba ang password O kung isa lang ang may laman
        if ((p1 || p2)) {
             // Kung may laman ang isa, pero hindi sila magkaparihas
            if (p1 !== p2) {
                err.style.color = 'red';
                err.textContent = 'New Password and Confirm Password must match!';
                return false;
            }
        }
        
        err.textContent = '';
        return true;
    }
});






// ang line nato ay para sa option don sa vehicle owner
document.getElementById("ownerSelect").addEventListener("change", function() {
    let text = this.options[this.selectedIndex].text; 
    document.getElementById("ownerName").value = text; 
});