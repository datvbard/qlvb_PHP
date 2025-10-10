import { useState, useEffect } from 'react';
import axios from 'axios';
import { API } from '@/App';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { toast } from 'sonner';
import { Plus, Pencil, Trash2, Menu as MenuIcon, GripVertical } from 'lucide-react';

const AdminMenu = () => {
  const [menuItems, setMenuItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const [showDialog, setShowDialog] = useState(false);
  const [editingMenu, setEditingMenu] = useState(null);
  const [formData, setFormData] = useState({
    title: '',
    path: '',
    icon: '📋',
    order: 0
  });

  useEffect(() => {
    fetchMenuItems();
  }, []);

  const fetchMenuItems = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`${API}/menu-items`);
      setMenuItems(response.data);
    } catch (error) {
      toast.error('Không thể tải menu');
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (editingMenu) {
        await axios.put(`${API}/menu-items/${editingMenu.id}`, formData);
        toast.success('Cập nhật menu thành công!');
      } else {
        await axios.post(`${API}/menu-items`, formData);
        toast.success('Thêm menu thành công!');
      }
      
      resetForm();
      setShowDialog(false);
      fetchMenuItems();
    } catch (error) {
      toast.error(error.response?.data?.detail || 'Có lỗi xảy ra');
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (menu) => {
    setEditingMenu(menu);
    setFormData({
      title: menu.title,
      path: menu.path,
      icon: menu.icon,
      order: menu.order
    });
    setShowDialog(true);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Bạn có chắc muốn xóa menu này?')) return;

    try {
      await axios.delete(`${API}/menu-items/${id}`);
      toast.success('Xóa menu thành công!');
      fetchMenuItems();
    } catch (error) {
      toast.error('Không thể xóa menu');
    }
  };

  const resetForm = () => {
    setFormData({
      title: '',
      path: '',
      icon: '📋',
      order: 0
    });
    setEditingMenu(null);
  };

  return (
    <div className="max-w-4xl mx-auto space-y-6" data-testid="admin-menu-page">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Quản Lý Menu</h1>
          <p className="text-gray-600 mt-1">Quản lý menu nghiệp vụ công tác Đảng</p>
        </div>
        <Dialog open={showDialog} onOpenChange={(open) => {
          setShowDialog(open);
          if (!open) resetForm();
        }}>
          <DialogTrigger asChild>
            <Button className="bg-burgundy-600 hover:bg-burgundy-700 gap-2">
              <Plus className="w-5 h-5" />
              Thêm Menu
            </Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>{editingMenu ? 'Chỉnh Sửa Menu' : 'Thêm Menu Mới'}</DialogTitle>
            </DialogHeader>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="title">Tiêu đề menu</Label>
                <Input
                  id="title"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="Ví dụ: Quản lý đảng viên"
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="path">Đường dẫn (path)</Label>
                <Input
                  id="path"
                  value={formData.path}
                  onChange={(e) => setFormData({ ...formData, path: e.target.value })}
                  placeholder="/dang-vien"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label htmlFor="icon">Biểu tượng (emoji)</Label>
                  <Input
                    id="icon"
                    value={formData.icon}
                    onChange={(e) => setFormData({ ...formData, icon: e.target.value })}
                    placeholder="📋"
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="order">Thứ tự</Label>
                  <Input
                    id="order"
                    type="number"
                    value={formData.order}
                    onChange={(e) => setFormData({ ...formData, order: parseInt(e.target.value) })}
                  />
                </div>
              </div>

              <div className="flex gap-3 pt-4">
                <Button
                  type="submit"
                  className="flex-1 bg-burgundy-600 hover:bg-burgundy-700"
                  disabled={loading}
                >
                  {loading ? 'Đang xử lý...' : editingMenu ? 'Cập Nhật' : 'Thêm'}
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => {
                    setShowDialog(false);
                    resetForm();
                  }}
                >
                  Hủy
                </Button>
              </div>
            </form>
          </DialogContent>
        </Dialog>
      </div>

      {/* Menu Items List */}
      <Card>
        <CardContent className="p-6">
          {menuItems.length === 0 ? (
            <div className="text-center py-12">
              <MenuIcon className="w-12 h-12 mx-auto mb-3 text-gray-300" />
              <p className="text-gray-500">Chưa có menu nào</p>
            </div>
          ) : (
            <div className="space-y-2">
              {menuItems.map((menu) => (
                <div
                  key={menu.id}
                  className="flex items-center gap-4 p-4 border border-gray-200 rounded-lg hover:border-burgundy-300 hover:bg-burgundy-50/30 transition-colors"
                >
                  <GripVertical className="w-5 h-5 text-gray-400" />
                  <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-burgundy-100 to-burgundy-200 flex items-center justify-center text-xl">
                    {menu.icon}
                  </div>
                  <div className="flex-1">
                    <h3 className="font-semibold text-gray-900">{menu.title}</h3>
                    <p className="text-sm text-gray-500">{menu.path}</p>
                  </div>
                  <div className="text-sm text-gray-500">
                    Thứ tự: {menu.order}
                  </div>
                  <div className="flex gap-2">
                    <Button
                      size="sm"
                      variant="ghost"
                      onClick={() => handleEdit(menu)}
                      className="text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                    >
                      <Pencil className="w-4 h-4" />
                    </Button>
                    <Button
                      size="sm"
                      variant="ghost"
                      onClick={() => handleDelete(menu.id)}
                      className="text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <Trash2 className="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Info */}
      <Card className="bg-blue-50 border-blue-200">
        <CardContent className="p-4">
          <p className="text-sm text-blue-800">
            <strong>Lưu ý:</strong> Menu sẽ hiển thị trên sidebar theo thứ tự bạn đã đặt. 
            Bạn có thể tạo các trang nghiệp vụ tương ứng cho mỗi menu item.
          </p>
        </CardContent>
      </Card>
    </div>
  );
};

export default AdminMenu;
