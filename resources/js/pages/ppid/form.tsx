import { Head, useForm } from '@inertiajs/react';
import { FileText, Upload, ArrowLeft } from 'lucide-react';

import PageContainer from '@/components/page-container';
import PageHeader from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import MainLayout from '@/layouts/main-layout';
import { FormEvent } from 'react';

interface PpidFormProps {
    auth?: {
        user?: {
            name: string;
            email: string;
        };
    };
}

export default function PpidForm({ auth }: PpidFormProps) {
    const { data, setData, post, processing, errors } = useForm({
        applicant_name: auth?.user?.name || '',
        nik: '',
        address: '',
        phone: '',
        email: auth?.user?.email || '',
        request_type: '',
        subject: '',
        description: '',
        priority: 'normal',
        attachments: [] as File[],
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        const formData = new FormData();
        formData.append('applicant_name', data.applicant_name);
        formData.append('nik', data.nik);
        formData.append('address', data.address);
        formData.append('phone', data.phone);
        formData.append('email', data.email);
        formData.append('request_type', data.request_type);
        formData.append('subject', data.subject);
        formData.append('description', data.description);
        formData.append('priority', data.priority);

        data.attachments.forEach((file) => {
            formData.append('attachments[]', file);
        });

        post('/ppid', {
            data: formData,
            forceFormData: true,
        });
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            const files = Array.from(e.target.files);
            setData('attachments', files);
        }
    };

    return (
        <MainLayout>
            <Head title="Formulir Permohonan Informasi">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            {/* Header */}
            <section className="bg-gradient-to-b from-primary/5 to-background py-8 md:py-12">
                <PageContainer>
                    <Button variant="ghost" className="mb-4" href="/ppid">
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Kembali
                    </Button>
                    <PageHeader
                        title="Formulir Permohonan Informasi Publik"
                        description="Lengkapi formulir di bawah ini untuk mengajukan permohonan informasi"
                    />
                </PageContainer>
            </section>

            {/* Form */}
            <section className="py-8">
                <PageContainer>
                    <form onSubmit={handleSubmit}>
                        <div className="space-y-6">
                            {/* Personal Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Informasi Pemohon</CardTitle>
                                    <CardDescription>
                                        Data diri Anda untuk keperluan komunikasi
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="applicant_name">
                                                Nama Lengkap <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="applicant_name"
                                                value={data.applicant_name}
                                                onChange={(e) =>
                                                    setData('applicant_name', e.target.value)
                                                }
                                                placeholder="Masukkan nama lengkap"
                                                required
                                            />
                                            {errors.applicant_name && (
                                                <p className="text-sm text-red-500">
                                                    {errors.applicant_name}
                                                </p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="nik">NIK (Opsional)</Label>
                                            <Input
                                                id="nik"
                                                value={data.nik}
                                                onChange={(e) => setData('nik', e.target.value)}
                                                placeholder="Masukkan NIK"
                                            />
                                            {errors.nik && (
                                                <p className="text-sm text-red-500">{errors.nik}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="address">Alamat</Label>
                                        <Textarea
                                            id="address"
                                            value={data.address}
                                            onChange={(e) => setData('address', e.target.value)}
                                            placeholder="Masukkan alamat lengkap"
                                            rows={2}
                                        />
                                        {errors.address && (
                                            <p className="text-sm text-red-500">{errors.address}</p>
                                        )}
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="phone">Nomor Telepon</Label>
                                            <Input
                                                id="phone"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                placeholder="Contoh: 081234567890"
                                            />
                                            {errors.phone && (
                                                <p className="text-sm text-red-500">{errors.phone}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email">
                                                Email <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                placeholder="nama@email.com"
                                                required
                                            />
                                            {errors.email && (
                                                <p className="text-sm text-red-500">{errors.email}</p>
                                            )}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Request Details */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Detail Permohonan</CardTitle>
                                    <CardDescription>
                                        Informasi yang Anda butuhkan
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="request_type">
                                            Jenis Permohonan <span className="text-red-500">*</span>
                                        </Label>
                                        <Select
                                            value={data.request_type}
                                            onValueChange={(value) => setData('request_type', value)}
                                            required
                                        >
                                            <SelectTrigger id="request_type">
                                                <SelectValue placeholder="Pilih jenis permohonan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="informasi_publik">
                                                    Informasi Publik
                                                </SelectItem>
                                                <SelectItem value="keberatan">Keberatan</SelectItem>
                                                <SelectItem value="sengketa">
                                                    Sengketa Informasi
                                                </SelectItem>
                                                <SelectItem value="lainnya">Lainnya</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.request_type && (
                                            <p className="text-sm text-red-500">{errors.request_type}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="subject">
                                            Subjek/Perihal <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="subject"
                                            value={data.subject}
                                            onChange={(e) => setData('subject', e.target.value)}
                                            placeholder="Contoh: Permohonan Data Putusan Tahun 2024"
                                            required
                                        />
                                        {errors.subject && (
                                            <p className="text-sm text-red-500">{errors.subject}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="description">
                                            Rincian Informasi <span className="text-red-500">*</span>
                                        </Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            placeholder="Jelaskan secara rinci informasi yang Anda butuhkan. Minimal 50 karakter."
                                            rows={6}
                                            required
                                            minLength={50}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Minimal 50 karakter
                                        </p>
                                        {errors.description && (
                                            <p className="text-sm text-red-500">{errors.description}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="priority">Prioritas</Label>
                                        <Select
                                            value={data.priority}
                                            onValueChange={(value) => setData('priority', value)}
                                        >
                                            <SelectTrigger id="priority">
                                                <SelectValue placeholder="Pilih prioritas" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="normal">Normal</SelectItem>
                                                <SelectItem value="high">Tinggi</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.priority && (
                                            <p className="text-sm text-red-500">{errors.priority}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Attachments */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Lampiran</CardTitle>
                                    <CardDescription>
                                        Dokumen pendukung (maksimal 5 file, 5MB per file)
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="attachments" className="cursor-pointer">
                                            <div className="flex items-center gap-2 rounded-lg border-2 border-dashed border-muted-foreground/25 p-8 transition-colors hover:border-primary/50 hover:bg-primary/5">
                                                <Upload className="h-8 w-8 text-muted-foreground" />
                                                <div className="flex-1">
                                                    <p className="font-medium">
                                                        Klik untuk upload atau drag & drop
                                                    </p>
                                                    <p className="text-sm text-muted-foreground">
                                                        PDF, DOC, DOCX, JPG, JPEG, PNG (maks. 5MB)
                                                    </p>
                                                </div>
                                            </div>
                                        </Label>
                                        <Input
                                            id="attachments"
                                            type="file"
                                            multiple
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            className="hidden"
                                            onChange={handleFileChange}
                                        />
                                        {errors.attachments && (
                                            <p className="text-sm text-red-500">{errors.attachments}</p>
                                        )}
                                    </div>

                                    {data.attachments.length > 0 && (
                                        <div className="space-y-2">
                                            <p className="text-sm font-medium">File terpilih:</p>
                                            <ul className="space-y-1">
                                                {data.attachments.map((file, index) => (
                                                    <li
                                                        key={index}
                                                        className="flex items-center gap-2 text-sm"
                                                    >
                                                        <FileText className="h-4 w-4 text-muted-foreground" />
                                                        <span>{file.name}</span>
                                                        <span className="text-xs text-muted-foreground">
                                                            ({(file.size / 1024 / 1024).toFixed(2)} MB)
                                                        </span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Submit */}
                            <Card className="border-primary bg-primary/5">
                                <CardContent className="pt-6">
                                    <div className="flex flex-wrap gap-4">
                                        <Button
                                            type="submit"
                                            size="lg"
                                            disabled={processing}
                                            className="flex-1 md:flex-none"
                                        >
                                            <FileText className="mr-2 h-5 w-5" />
                                            {processing ? 'Memproses...' : 'Kirim Permohonan'}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="lg"
                                            href="/ppid"
                                        >
                                            Batal
                                        </Button>
                                    </div>
                                    <p className="mt-4 text-center text-sm text-muted-foreground">
                                        Dengan mengirim formulir ini, Anda menyetujui syarat dan
                                        ketentuan layanan PPID
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </form>
                </PageContainer>
            </section>
        </MainLayout>
    );
}
