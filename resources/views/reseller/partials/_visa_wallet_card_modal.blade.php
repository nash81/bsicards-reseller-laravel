<!-- This file is deprecated and should be removed. Use the new Digital Visa Wallet Card modal in the main views. -->

<!-- Modal for Create Card -->
<div class="modal fade" id="createCardModal" tabindex="-1" role="dialog" aria-labelledby="createCardModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createCardModalLabel">Create Digital Visa Wallet Card</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="createCardForm">
          <div class="form-group">
            <label for="firstname">First Name</label>
            <input type="text" class="form-control" id="firstname" name="firstname" required>
          </div>
          <div class="form-group">
            <label for="lastname">Last Name</label>
            <input type="text" class="form-control" id="lastname" name="lastname" required>
          </div>
          <div class="form-group">
            <label for="useremail">Email</label>
            <input type="email" class="form-control" id="useremail" name="useremail" required>
          </div>
          <button type="submit" class="btn btn-success">Create Card</button>
        </form>
      </div>
    </div>
  </div>
</div>

