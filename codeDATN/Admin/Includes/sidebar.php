 <ul class="navbar-nav sidebar sidebar-light accordion " id="accordionSidebar">
     <a class="sidebar-brand d-flex align-items-center bg-gradient-primary  justify-content-center" href="index.php">
         <div class="sidebar-brand-icon">
             <img src="img/logo/TL.png">
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
     
     <li class="nav-item">
         <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrap" aria-expanded="true" aria-controls="collapseBootstrap">
             <i class="fas fa-chalkboard"></i>
             <span>Quản lý môn học</span>
         </a>
         <div id="collapseBootstrap" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
             <div class="bg-white py-2 collapse-inner rounded">
                 
                 <a class="collapse-item" href="createClass.php">Tạo môn học</a>
                 <!-- <a class="collapse-item" href="#">Member List</a> -->
             </div>
         </div>
     </li>
     <hr class="sidebar-divider">
     <li class="nav-item">
         <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrapusers" aria-expanded="true" aria-controls="collapseBootstrapusers">
             <i class="fas fa-code-branch"></i>
             <span>Quản lý lớp học</span>
         </a>
         <div id="collapseBootstrapusers" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
             <div class="bg-white py-2 collapse-inner rounded">
                 
                 <a class="collapse-item" href="createClassArms.php">Tạo lớp học</a>
                 <!-- <a class="collapse-item" href="usersList.php">User List</a> -->
             </div>
         </div>
     </li>
     <hr class="sidebar-divider">
     <li class="nav-item">
         <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrapassests" aria-expanded="true" aria-controls="collapseBootstrapassests">
             <i class="fas fa-chalkboard-teacher"></i>
             <span>Quản lý giáo viên</span>
         </a>
         <div id="collapseBootstrapassests" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
             <div class="bg-white py-2 collapse-inner rounded">
                 <a class="collapse-item" href="createClassTeacher.php">Thêm giáo viên mới</a>
                 <!-- <a class="collapse-item" href="assetsCategoryList.php">Assets Category List</a>
             <a class="collapse-item" href="createAssets.php">Create Assets</a> -->
             </div>
         </div>
     </li>
     <!-- <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrapschemes"
          aria-expanded="true" aria-controls="collapseBootstrapschemes">
          <i class="fas fa-home"></i>
          <span>Manage Schemes</span>
        </a>
        <div id="collapseBootstrapschemes" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Manage Schemes</h6>
             <a class="collapse-item" href="createSchemes.php">Create Scheme</a>
            <a class="collapse-item" href="schemeList.php">Scheme List</a>
          </div>
        </div>
      </li> -->

     <hr class="sidebar-divider">
     </li>
     <li class="nav-item">
         <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrap2" aria-expanded="true" aria-controls="collapseBootstrap2">
             <i class="fas fa-user-graduate"></i>
             <span>Quản lý sinh viên</span>
         </a>
         <div id="collapseBootstrap2" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
             <div class="bg-white py-2 collapse-inner rounded">
                 <a class="collapse-item" href="createStudents.php">Thêm sinh viên mới</a>
                 <!-- <a class="collapse-item" href="#">Assets Type</a> -->
             </div>
         </div>
     </li>

     <hr class="sidebar-divider">
     </li>
     <li class="nav-item">
         <!-- <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBootstrapcon" aria-expanded="true" aria-controls="collapseBootstrapcon">
             
             <span></span>
         </a> -->
         <div id="collapseBootstrapcon" class="collapse" aria-labelledby="headingBootstrap" data-parent="#accordionSidebar">
             <div class="bg-white py-2 collapse-inner rounded">
                
                 <a class="collapse-item" href="createSessionTerm.php"></a>
                 <!-- <a class="collapse-item" href="addMemberToContLevel.php ">Add Member to Level</a> -->
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

 </ul>