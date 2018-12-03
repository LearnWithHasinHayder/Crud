<?php
define( 'DB_NAME', 'data/db.txt' );
function seed() {
	$data           = array(
		array(
			'id'    => 1,
			'fname' => 'Kamal',
			'lname' => 'Ahmed',
			'roll'  => '11'
		),
		array(
			'id'    => 2,
			'fname' => 'Jamal',
			'lname' => 'Ahmed',
			'roll'  => '12'
		),
		array(
			'id'    => 3,
			'fname' => 'Ripon',
			'lname' => 'Ahmed',
			'roll'  => '9'
		),
		array(
			'id'    => 4,
			'fname' => 'Nikhil',
			'lname' => 'Chandra',
			'roll'  => '8'
		),
		array(
			'id'    => 5,
			'fname' => 'John',
			'lname' => 'Rozario',
			'roll'  => '7'
		),
	);
	$serializedData = serialize( $data );
	file_put_contents( DB_NAME, $serializedData, LOCK_EX );
}

function generateReport() {
	$serialziedData = file_get_contents( DB_NAME );
	$students       = unserialize( $serialziedData );
	?>
    <table>
        <tr>
            <th>Name</th>
            <th>Roll</th>
			<?php if ( isAdmin() || isEditor() ): ?>
                <th width="25%">Action</th>
			<?php endif; ?>
        </tr>
		<?php
		foreach ( $students as $student ) {
			?>
            <tr>
                <td><?php printf( '%s %s', $student['fname'], $student['lname'] ); ?></td>
                <td><?php printf( '%s', $student['roll'] ); ?></td>
				<?php if ( isAdmin() ): ?>
                    <td><?php printf( '<a href="/crud/index.php?task=edit&id=%s">Edit</a> | <a class="delete" href="/crud/index.php?task=delete&id=%s">Delete</a>', $student['id'], $student['id'] ); ?></td>
				<?php elseif ( isEditor() ): ?>
                    <td><?php printf( '<a href="/crud/index.php?task=edit&id=%s">Edit</a>', $student['id'] ); ?></td>
				<?php endif; ?>
            </tr>
			<?
		}
		?>

    </table>
	<?php
}

function addStudent( $fname, $lname, $roll ) {
	$found          = false;
	$serialziedData = file_get_contents( DB_NAME );
	$students       = unserialize( $serialziedData );
	foreach ( $students as $_student ) {
		if ( $_student['roll'] == $roll ) {
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		$newId   = getNewId( $students );
		$student = array(
			'id'    => $newId,
			'fname' => $fname,
			'lname' => $lname,
			'roll'  => $roll
		);
		array_push( $students, $student );
		$serializedData = serialize( $students );
		file_put_contents( DB_NAME, $serializedData, LOCK_EX );

		return true;
	}

	return false;
}

function getStudent( $id ) {
	$serialziedData = file_get_contents( DB_NAME );
	$students       = unserialize( $serialziedData );
	foreach ( $students as $student ) {
		if ( $student['id'] == $id ) {
			return $student;
		}
	}

	return false;
}

function updateStudent( $id, $fname, $lname, $roll ) {
	$found          = false;
	$serialziedData = file_get_contents( DB_NAME );
	$students       = unserialize( $serialziedData );
	foreach ( $students as $_student ) {
		if ( $_student['roll'] == $roll && $_student['id'] != $id ) {
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		$students[ $id - 1 ]['fname'] = $fname;
		$students[ $id - 1 ]['lname'] = $lname;
		$students[ $id - 1 ]['roll']  = $roll;
		$serializedData               = serialize( $students );
		file_put_contents( DB_NAME, $serializedData, LOCK_EX );

		return true;
	}

	return false;
}

function deleteStudent( $id ) {
	$serialziedData = file_get_contents( DB_NAME );
	$students       = unserialize( $serialziedData );

	foreach ( $students as $offset => $student ) {
		if ( $student['id'] == $id ) {
			unset( $students[ $offset ] );
		}

	}
	$serializedData = serialize( $students );
	file_put_contents( DB_NAME, $serializedData, LOCK_EX );
}

function printRaw() {
	$serialziedData = file_get_contents( DB_NAME );
	$students       = unserialize( $serialziedData );
	print_r( $students );
}

function getNewId( $students ) {
	$maxId = max( array_column( $students, 'id' ) );

	return $maxId + 1;
}


function isAdmin() {
	return ( 'admin' == $_SESSION['role'] );
}

function isEditor() {
	return ( 'editor' == $_SESSION['role'] );
}

function hasPrivilege(){
    return (isAdmin() || isEditor());
}