@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-plus-circle me-2 text-white"></i>Create Category</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.product-categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">

                    {{-- Category Name --}}
                    <div class="col-md-6">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Parent Category --}}
                    <div class="col-md-6">
                        <label class="form-label">Parent Category</label>
                        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">-- None --</option>
                            @foreach($categories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id')==$parent->id?'selected':'' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="Active" {{ old('status')=='Active'?'selected':'' }}>Active</option>
                            <option value="Inactive" {{ old('status')=='Inactive'?'selected':'' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Image --}}
                    <div class="col-md-6">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        {{-- Image Preview --}}
                        <div class="mt-2">
                            <img id="preview-image" src="#" alt="Image Preview" class="img-fluid rounded shadow-sm" style="display:none; max-height:150px;">
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                     <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Real-time image preview
    document.getElementById('image').addEventListener('change', function(event){
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('preview-image');
            output.src = reader.result;
            output.style.display = 'block';
        };
        if(event.target.files[0]){
            reader.readAsDataURL(event.target.files[0]);
        }
    });
</script>
@endsection
