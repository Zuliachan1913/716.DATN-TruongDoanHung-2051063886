 <ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
      <a class="sidebar-brand d-flex align-items-center bg-gradient-primary justify-content-center" href="index.php">
        <div class="sidebar-brand-icon" >
          <img src="img/logo/Logo-Thuy_Loi.png">
        </div>
        <div class="sidebar-brand-text mx-3">Học viện TL</div>
      </a>
      <hr class="sidebar-divider my-0">
      <li class="nav-item active">
        <a class="nav-link" href="index.php">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Dashboard</span></a>
      </li> 
      <hr class="sidebar-divider">
     
      </li>
       <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrap2"
          aria-expanded="true" aria-controls="collapseBootstrap2">
          <i class="fas fa-user-graduate"></i>
          <span>Quản lý sinh viên</span>
        </a>
        <div id="collapseBootstrap2" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">

            <a class="collapse-item" href="viewStudents.php">Danh sách sinh viên</a>
            <!-- <a class="collapse-item" href="#">Assets Type</a> -->
          </div>
        </div>
      </li>
      <hr class="sidebar-divider">

      </li>
       <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrapcon"
          aria-expanded="true" aria-controls="collapseBootstrapcon">
          <i class="fa fa-calendar-alt"></i>
          <span>Quản lý điểm danh</span>
        </a>
        <div id="collapseBootstrapcon" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="viewStudentAttendance.php">Xem học sinh điểm danh</a>
            <!-- <a class="collapse-item" href="addMemberToContLevel.php ">Add Member to Level</a> -->
          </div>
        </div>
      </li>
      <hr class="sidebar-divider">
      
        <li class="nav-item">
    <a class="nav-link collapsed"
       href="#"
       data-toggle="collapse"
       data-target="#collapseQR"
       aria-expanded="true"
       aria-controls="collapseQR">

        <i class="fa fa-qrcode"></i>
        <span>Điểm danh QR</span>

    </a>

    <div id="collapseQR"
         class="collapse"
         aria-labelledby="headingQR"
         data-parent="#accordionSidebar">

        <div class="bg-white py-2 collapse-inner rounded">

            <a class="collapse-item" href="generateQR.php">
                Tạo mã QR
            </a>

        </div>
    </div>
</li>
      <hr class="sidebar-divider">
      <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
          <i class="fas fa-chart-line"></i>
          <span>Phân Tích</span>
        </a>
      </li>
      <hr class="sidebar-divider">
      <li class="nav-item">
        <a class="nav-link" href="chatbot.php">
          <i class="fas fa-robot"></i>
          <span>Chatbot AI</span>
        </a>
      </li>
    </ul>