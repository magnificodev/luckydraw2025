                </div>
            </main>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-group">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Xác nhận đăng xuất</h3>
                </div>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                <button class="btn btn-danger" onclick="performLogout()">Đăng xuất</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
