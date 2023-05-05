<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Book Seats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center">
            <h3>Book Tickets</h3>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger">
                <p>{{ session('error') }}</p>
            </div>
        @elseif (session()->has('success'))
            <div class="alert alert-success">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        <form class="" action="/book-seats" method="post">
            @csrf
            <div class="row mb-2">
                <div class="col-md-3 col-lg-2 text-end">
                    <label for="seat-name">Seat Name</label>
                </div>
                <div class="col-md-3 col-lg-2">
                    <input type="text" id="seat-name" name="seat-name" class="form-control">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 col-lg-2 text-end">
                    <label for="total-seats">Total Seats</label>
                </div>
                <div class="col-md-3 col-lg-2">
                    <input type="number" id="total-seats" name="total-seats" class="form-control">
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 col-lg-2"></div>
                <div class="col-md-3 col-lg-2">
                    <button class="btn btn-primary" type="submit">BOOK</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
