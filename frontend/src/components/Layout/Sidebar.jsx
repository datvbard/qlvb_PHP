import { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { API } from '@/App';
import { Button } from '@/components/ui/button';
import { 
  FileText, 
  LayoutDashboard, 
  FolderOpen, 
  Menu as MenuIcon, 
  Settings,
  LogOut,
  ChevronDown,
  ChevronRight,
  User
} from 'lucide-react';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

const Sidebar = ({ user, onLogout, isOpen, setIsOpen }) => {
  const location = useLocation();
  const navigate = useNavigate();
  const [menuItems, setMenuItems] = useState([]);
  const [adminOpen, setAdminOpen] = useState(false);

  useEffect(() => {
    fetchMenuItems();
  }, []);

  const fetchMenuItems = async () => {
    try {
      const response = await axios.get(`${API}/menu-items`);
      setMenuItems(response.data);
    } catch (error) {
      console.error('Error fetching menu items:', error);
    }
  };

  const isActive = (path) => location.pathname === path;

  const NavLink = ({ to, icon: Icon, children, badge }) => (
    <Link
      to={to}
      className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
        isActive(to)
          ? 'bg-burgundy-600 text-white'
          : 'text-gray-700 hover:bg-gray-100'
      }`}
      data-testid={`nav-${to.replace(/\//g, '-')}`}
    >
      <Icon className="w-5 h-5" />
      <span className="font-medium">{children}</span>
      {badge && (
        <span className="ml-auto bg-burgundy-600 text-white text-xs px-2 py-0.5 rounded-full">
          {badge}
        </span>
      )}
    </Link>
  );

  return (
    <>
      {/* Mobile overlay */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black/50 z-40 lg:hidden"
          onClick={() => setIsOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={`fixed top-0 left-0 h-full w-64 bg-white border-r border-gray-200 z-50 transform transition-transform duration-300 lg:translate-x-0 ${
          isOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
        data-testid="sidebar"
      >
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center gap-3 px-4 py-6 border-b border-gray-200">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-burgundy-500 to-burgundy-700 flex items-center justify-center shadow-md">
              <FileText className="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 className="text-lg font-bold text-gray-900">Quản Lý Văn Bản</h1>
            </div>
          </div>

          {/* User Info */}
          <div className="px-4 py-4 border-b border-gray-200">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-burgundy-100 flex items-center justify-center">
                <User className="w-5 h-5 text-burgundy-700" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-semibold text-gray-900 truncate">{user?.name}</p>
                <p className="text-xs text-gray-500">{user?.role === 'admin' ? 'Quản trị viên' : 'Người dùng'}</p>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <NavLink to="/dashboard" icon={LayoutDashboard}>
              Tổng quan
            </NavLink>

            <NavLink to="/documents" icon={FileText}>
              Văn bản
            </NavLink>

            {/* Dynamic Menu Items */}
            {menuItems.filter(item => !item.parent_id && !item.path.includes('/admin')).map((item) => (
              <NavLink key={item.id} to={item.path} icon={FolderOpen}>
                {item.title}
              </NavLink>
            ))}

            {/* Admin Section */}
            {user?.role === 'admin' && (
              <Collapsible open={adminOpen} onOpenChange={setAdminOpen}>
                <CollapsibleTrigger asChild>
                  <button
                    className={`w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
                      location.pathname.includes('/admin')
                        ? 'bg-burgundy-50 text-burgundy-700'
                        : 'text-gray-700 hover:bg-gray-100'
                    }`}
                  >
                    <Settings className="w-5 h-5" />
                    <span className="font-medium flex-1 text-left">Quản trị</span>
                    {adminOpen ? (
                      <ChevronDown className="w-4 h-4" />
                    ) : (
                      <ChevronRight className="w-4 h-4" />
                    )}
                  </button>
                </CollapsibleTrigger>
                <CollapsibleContent className="pl-4 space-y-1 mt-1">
                  <Link
                    to="/admin/categories"
                    className={`flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-colors ${
                      isActive('/admin/categories')
                        ? 'bg-burgundy-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100'
                    }`}
                  >
                    <FolderOpen className="w-4 h-4" />
                    Quản lý danh mục
                  </Link>
                  <Link
                    to="/admin/menu"
                    className={`flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-colors ${
                      isActive('/admin/menu')
                        ? 'bg-burgundy-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100'
                    }`}
                  >
                    <MenuIcon className="w-4 h-4" />
                    Quản lý menu
                  </Link>
                </CollapsibleContent>
              </Collapsible>
            )}
          </nav>

          {/* Logout Button */}
          <div className="p-4 border-t border-gray-200">
            <Button
              onClick={onLogout}
              variant="outline"
              className="w-full gap-2 border-burgundy-200 text-burgundy-700 hover:bg-burgundy-50"
              data-testid="sidebar-logout-button"
            >
              <LogOut className="w-4 h-4" />
              Đăng xuất
            </Button>
          </div>
        </div>
      </aside>
    </>
  );
};

export default Sidebar;
