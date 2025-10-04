import { useState, useEffect } from 'react';
import axios from 'axios';
import { API } from '@/App';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { toast } from 'sonner';
import { 
  FileText, 
  Plus, 
  Search, 
  Download, 
  LogOut, 
  Pencil, 
  Trash2,
  Filter,
  Clock,
  CheckCircle,
  AlertCircle,
  XCircle,
  User
} from 'lucide-react';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { format } from 'date-fns';
import { vi } from 'date-fns/locale';

const Dashboard = ({ user, onLogout }) => {
  const [stats, setStats] = useState({ total: 0, active: 0, expiring: 0, expired: 0 });
  const [documents, setDocuments] = useState([]);
  const [filteredDocs, setFilteredDocs] = useState([]);
  const [documentTypes, setDocumentTypes] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState('all');
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [editingDoc, setEditingDoc] = useState(null);
  const [formData, setFormData] = useState({
    code: '',
    title: '',
    doc_type: '',
    assignee: '',
    expiry_date: '',
    summary: ''
  });
  const [selectedDate, setSelectedDate] = useState(undefined);

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
    filterDocuments();
  }, [documents, searchTerm, filterType]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const [statsRes, docsRes, typesRes] = await Promise.all([
        axios.get(`${API}/documents/stats`),
        axios.get(`${API}/documents`),
        axios.get(`${API}/document-types`)
      ]);
      setStats(statsRes.data);
      setDocuments(docsRes.data);
      setDocumentTypes(typesRes.data);
    } catch (error) {
      toast.error('Không thể tải dữ liệu');
    } finally {
      setLoading(false);
    }
  };

  const filterDocuments = () => {
    let filtered = [...documents];

    if (filterType !== 'all') {
      filtered = filtered.filter(doc => doc.doc_type === filterType);
    }

    if (searchTerm) {
      const search = searchTerm.toLowerCase();
      filtered = filtered.filter(doc =>
        doc.code.toLowerCase().includes(search) ||
        doc.title.toLowerCase().includes(search) ||
        doc.assignee.toLowerCase().includes(search) ||
        doc.summary.toLowerCase().includes(search)
      );
    }

    setFilteredDocs(filtered);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (editingDoc) {
        await axios.put(`${API}/documents/${editingDoc.id}`, formData);
        toast.success('Cập nhật văn bản thành công!');
      } else {
        await axios.post(`${API}/documents`, formData);
        toast.success('Thêm văn bản thành công!');
      }
      
      resetForm();
      setShowAddDialog(false);
      setEditingDoc(null);
      fetchData();
    } catch (error) {
      toast.error(error.response?.data?.detail || 'Có lỗi xảy ra');
    } finally {
      setLoading(false);
    }
  };

  const handleEdit = (doc) => {
    setEditingDoc(doc);
    setFormData({
      code: doc.code,
      title: doc.title,
      doc_type: doc.doc_type,
      assignee: doc.assignee,
      expiry_date: doc.expiry_date,
      summary: doc.summary
    });
    setSelectedDate(new Date(doc.expiry_date));
    setShowAddDialog(true);
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Bạn có chắc muốn xóa văn bản này?')) return;

    try {
      await axios.delete(`${API}/documents/${id}`);
      toast.success('Xóa văn bản thành công!');
      fetchData();
    } catch (error) {
      toast.error('Không thể xóa văn bản');
    }
  };

  const handleExport = async () => {
    try {
      const response = await axios.get(`${API}/documents/export`, {
        responseType: 'blob'
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', 'danh-sach-van-ban.xlsx');
      document.body.appendChild(link);
      link.click();
      link.remove();
      
      toast.success('Xuất file thành công!');
    } catch (error) {
      toast.error('Không thể xuất file');
    }
  };

  const resetForm = () => {
    setFormData({
      code: '',
      title: '',
      doc_type: '',
      assignee: '',
      expiry_date: '',
      summary: ''
    });
    setSelectedDate(undefined);
  };

  const getStatusBadge = (status) => {
    const badges = {
      active: { bg: 'bg-emerald-100', text: 'text-emerald-700', label: 'Còn hạn', icon: CheckCircle },
      expiring: { bg: 'bg-amber-100', text: 'text-amber-700', label: 'Sắp hết hạn', icon: AlertCircle },
      expired: { bg: 'bg-red-100', text: 'text-red-700', label: 'Đã hết hạn', icon: XCircle }
    };
    const badge = badges[status] || badges.active;
    const Icon = badge.icon;
    return (
      <span className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold ${badge.bg} ${badge.text}`}>
        <Icon className="w-3.5 h-3.5" />
        {badge.label}
      </span>
    );
  };

  const getTypeLabel = (type) => {
    const docType = documentTypes.find(t => t.value === type);
    return docType ? docType.label : type;
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100" data-testid="dashboard">
      {/* Header */}
      <header className="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-burgundy-500 to-burgundy-700 flex items-center justify-center shadow-md">
                <FileText className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Quản Lý Văn Bản</h1>
                <p className="text-sm text-gray-600">Hệ thống theo dõi và quản lý</p>
              </div>
            </div>
            <div className="flex items-center gap-4">
              <div className="flex items-center gap-2 px-4 py-2 bg-gray-50 rounded-lg">
                <User className="w-4 h-4 text-gray-600" />
                <span className="text-sm font-medium text-gray-700">{user?.name}</span>
              </div>
              <Button
                onClick={onLogout}
                variant="outline"
                className="gap-2 border-burgundy-200 text-burgundy-700 hover:bg-burgundy-50"
                data-testid="logout-button"
              >
                <LogOut className="w-4 h-4" />
                Đăng xuất
              </Button>
            </div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <Card className="bg-gradient-to-br from-burgundy-500 to-burgundy-700 text-white border-0 shadow-lg hover:shadow-xl transition-shadow" data-testid="total-stat">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-white/90 text-sm font-medium mb-1">TỔNG VĂN BẢN</p>
                  <p className="text-4xl font-bold text-white">{stats.total}</p>
                </div>
                <FileText className="w-12 h-12 text-white/80" />
              </div>
            </CardContent>
          </Card>

          <Card className="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white border-0 shadow-lg hover:shadow-xl transition-shadow" data-testid="active-stat">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-emerald-100 text-sm font-medium mb-1">CÒN HẠN</p>
                  <p className="text-4xl font-bold">{stats.active}</p>
                </div>
                <CheckCircle className="w-12 h-12 text-emerald-200" />
              </div>
            </CardContent>
          </Card>

          <Card className="bg-gradient-to-br from-amber-500 to-amber-600 text-white border-0 shadow-lg hover:shadow-xl transition-shadow" data-testid="expiring-stat">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-amber-100 text-sm font-medium mb-1">SẮP HẾT HẠN</p>
                  <p className="text-4xl font-bold">{stats.expiring}</p>
                </div>
                <Clock className="w-12 h-12 text-amber-200" />
              </div>
            </CardContent>
          </Card>

          <Card className="bg-gradient-to-br from-red-500 to-red-600 text-white border-0 shadow-lg hover:shadow-xl transition-shadow" data-testid="expired-stat">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-red-100 text-sm font-medium mb-1">ĐÃ QUA HẠN</p>
                  <p className="text-4xl font-bold">{stats.expired}</p>
                </div>
                <XCircle className="w-12 h-12 text-red-200" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Actions Bar */}
        <div className="flex flex-col md:flex-row gap-4 mb-6">
          <Dialog open={showAddDialog} onOpenChange={(open) => {
            setShowAddDialog(open);
            if (!open) {
              setEditingDoc(null);
              resetForm();
            }
          }}>
            <DialogTrigger asChild>
              <Button className="bg-burgundy-600 hover:bg-burgundy-700 text-white gap-2 shadow-md" data-testid="add-document-button">
                <Plus className="w-5 h-5" />
                Thêm Văn Bản Mới
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
              <DialogHeader>
                <DialogTitle className="text-2xl font-bold">
                  {editingDoc ? 'Chỉnh Sửa Văn Bản' : 'Thêm Văn Bản Mới'}
                </DialogTitle>
              </DialogHeader>
              <form onSubmit={handleSubmit} className="space-y-4 mt-4">
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="code">Mã Văn Bản / Hợp đồng</Label>
                    <Input
                      id="code"
                      value={formData.code}
                      onChange={(e) => setFormData({ ...formData, code: e.target.value })}
                      placeholder="Ví dụ: 330/TCT-QLĐT-XA"
                      required
                      data-testid="code-input"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="doc_type">Loại văn bản</Label>
                    <Select
                      value={formData.doc_type}
                      onValueChange={(value) => setFormData({ ...formData, doc_type: value })}
                      required
                    >
                      <SelectTrigger data-testid="doc-type-select">
                        <SelectValue placeholder="-- Chọn loại văn bản --" />
                      </SelectTrigger>
                      <SelectContent>
                        {documentTypes.map(type => (
                          <SelectItem key={type.value} value={type.value}>
                            {type.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="title">Tên văn bản / Hợp đồng</Label>
                  <Input
                    id="title"
                    value={formData.title}
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    placeholder="Ví dụ: Hợp đồng thuê nhà ABC"
                    required
                    data-testid="title-input"
                  />
                </div>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="assignee">Cán bộ cập nhật</Label>
                    <Input
                      id="assignee"
                      value={formData.assignee}
                      onChange={(e) => setFormData({ ...formData, assignee: e.target.value })}
                      placeholder="Nhập tên người cập nhật"
                      required
                      data-testid="assignee-input"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label>Ngày hết hạn</Label>
                    <Popover>
                      <PopoverTrigger asChild>
                        <Button
                          variant="outline"
                          className="w-full justify-start text-left font-normal"
                          data-testid="expiry-date-button"
                        >
                          <Clock className="mr-2 h-4 w-4" />
                          {formData.expiry_date ? format(new Date(formData.expiry_date), 'dd/MM/yyyy', { locale: vi }) : 'dd/mm/yyyy'}
                        </Button>
                      </PopoverTrigger>
                      <PopoverContent className="w-auto p-0" align="start">
                        <Calendar
                          mode="single"
                          selected={selectedDate}
                          onSelect={(date) => {
                            if (date) {
                              setSelectedDate(date);
                              setFormData({ ...formData, expiry_date: date.toISOString().split('T')[0] });
                            }
                          }}
                          locale={vi}
                          initialFocus
                        />
                      </PopoverContent>
                    </Popover>
                  </div>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="summary">Nội dung tóm tắt</Label>
                  <Textarea
                    id="summary"
                    value={formData.summary}
                    onChange={(e) => setFormData({ ...formData, summary: e.target.value })}
                    placeholder="Nhập tóm tắt nội dung chính của văn bản..."
                    rows={4}
                    required
                    data-testid="summary-input"
                  />
                </div>

                <div className="flex gap-3 pt-4">
                  <Button
                    type="submit"
                    className="flex-1 bg-burgundy-600 hover:bg-burgundy-700 text-white"
                    disabled={loading}
                    data-testid="submit-document-button"
                  >
                    {loading ? 'Đang xử lý...' : editingDoc ? 'Cập Nhật' : 'Thêm Văn Bản'}
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => {
                      setShowAddDialog(false);
                      setEditingDoc(null);
                      resetForm();
                    }}
                    data-testid="cancel-button"
                  >
                    Hủy
                  </Button>
                </div>
              </form>
            </DialogContent>
          </Dialog>

          <Button onClick={handleExport} variant="outline" className="gap-2 border-burgundy-200 text-burgundy-700 hover:bg-burgundy-50" data-testid="export-button">
            <Download className="w-4 h-4" />
            Xuất File
          </Button>
        </div>

        {/* Filter and Search */}
        <Card className="mb-6 shadow-md border-0">
          <CardContent className="p-6">
            <div className="flex items-center gap-2 mb-4">
              <Filter className="w-5 h-5 text-burgundy-600" />
              <h2 className="text-lg font-bold text-gray-900">Bộ Lọc & Tìm Kiếm</h2>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>Lọc theo loại văn bản</Label>
                <Select value={filterType} onValueChange={setFilterType}>
                  <SelectTrigger data-testid="filter-type-select">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Tất cả</SelectItem>
                    {documentTypes.map(type => (
                      <SelectItem key={type.value} value={type.value}>
                        {type.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Tìm kiếm nhanh</Label>
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                  <Input
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    placeholder="Tìm theo tên văn bản, cán bộ..."
                    className="pl-10"
                    data-testid="search-input"
                  />
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Documents Table */}
        <Card className="shadow-lg border-0">
          <CardContent className="p-0">
            <div className="overflow-x-auto">
              <table className="w-full" data-testid="documents-table">
                <thead className="bg-gradient-to-r from-burgundy-50 to-burgundy-100 border-b-2 border-burgundy-200">
                  <tr>
                    <th className="px-6 py-4 text-left text-xs font-bold text-burgundy-900 uppercase tracking-wider">Mã Văn Bản</th>
                    <th className="px-6 py-4 text-left text-xs font-bold text-burgundy-900 uppercase tracking-wider">Tóm Tắt</th>
                    <th className="px-6 py-4 text-left text-xs font-bold text-burgundy-900 uppercase tracking-wider">Loại Văn Bản</th>
                    <th className="px-6 py-4 text-left text-xs font-bold text-burgundy-900 uppercase tracking-wider">Ngày Hết Hạn</th>
                    <th className="px-6 py-4 text-left text-xs font-bold text-burgundy-900 uppercase tracking-wider">Trạng Thái</th>
                    <th className="px-6 py-4 text-left text-xs font-bold text-burgundy-900 uppercase tracking-wider">Cán Bộ Cập Nhật</th>
                    <th className="px-6 py-4 text-left text-xs font-bold text-burgundy-900 uppercase tracking-wider">Hành Động</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {filteredDocs.length === 0 ? (
                    <tr>
                      <td colSpan="7" className="px-6 py-12 text-center text-gray-500">
                        <FileText className="w-12 h-12 mx-auto mb-3 text-gray-300" />
                        <p className="text-lg font-medium">Không có văn bản nào</p>
                        <p className="text-sm">Thêm văn bản mới để bắt đầu</p>
                      </td>
                    </tr>
                  ) : (
                    filteredDocs.map((doc) => (
                      <tr key={doc.id} className="hover:bg-gray-50 transition-colors" data-testid="document-row">
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="font-semibold text-gray-900">{doc.code}</div>
                          <div className="text-sm text-gray-500 mt-0.5">{doc.title}</div>
                        </td>
                        <td className="px-6 py-4">
                          <div className="text-sm text-gray-700 max-w-xs truncate">{doc.summary}</div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {getTypeLabel(doc.doc_type)}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                          {format(new Date(doc.expiry_date), 'dd/MM/yyyy', { locale: vi })}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          {getStatusBadge(doc.status)}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center gap-2">
                            <div className="w-8 h-8 rounded-full bg-burgundy-100 flex items-center justify-center">
                              <User className="w-4 h-4 text-burgundy-700" />
                            </div>
                            <span className="text-sm font-medium text-gray-700">{doc.assignee}</span>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex gap-2">
                            <Button
                              size="sm"
                              variant="ghost"
                              onClick={() => handleEdit(doc)}
                              className="text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                              data-testid="edit-button"
                            >
                              <Pencil className="w-4 h-4" />
                            </Button>
                            <Button
                              size="sm"
                              variant="ghost"
                              onClick={() => handleDelete(doc.id)}
                              className="text-red-600 hover:text-red-700 hover:bg-red-50"
                              data-testid="delete-button"
                            >
                              <Trash2 className="w-4 h-4" />
                            </Button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </main>

      {/* Footer */}
      <footer className="bg-white border-t border-gray-200 mt-12 py-6">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <p className="text-center text-sm text-gray-500">© 2025 Hệ Thống Quản Lý Văn Bản. All rights reserved.</p>
        </div>
      </footer>
    </div>
  );
};

export default Dashboard;
