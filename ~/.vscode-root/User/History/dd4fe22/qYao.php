<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tabla</title>
</head>
<body>
    <h1>Tabla de prueba</h1>
    <table>
        <thead>
            <tr>
                <th>id</th>
                <th>name</th>
                <th>type</th>
                <th>status</th>
                <th>cpu</th>
                <th>maxcpu</th>
                <th>mem</th>
                <th>maxmem</th>
                <th>netin</th>
                <th>netout</th>
                <th>disk</th>
                <th>diskread</th>
                <th>diskwrite</th>
                <th>uptime</th>
                <th>updated_at</th>
                <th>created_at</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tablas as $tabla)
            <tr>
                <td>{{ $tabla->id }}</td>
                <td>{{ $tabla->name }}</td>
                <td>{{ $tabla->type }}</td>
                <td>{{ $tabla->status }}</td>
                <td>{{ $tabla->cpu }}</td>
                <td>{{ $tabla->maxcpu }}</td>
                <td>{{ $tabla->mem }}</td>
                <td>{{ $tabla->maxmem }}</td>
                <td>{{ $tabla->netin }}</td>
                <td>{{ $tabla->netout }}</td>
                <td>{{ $tabla->disk }}</td>
                <td>{{ $tabla->diskread }}</td>
                <td>{{ $tabla->diskwrite }}</td>
                <td>{{ $tabla->uptime }}</td>
                <td>{{ $tabla->updated_at }}</td>
                <td>{{ $tabla->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
        
</body>
</html>

