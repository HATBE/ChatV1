<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Encrypted chat</title>
    <?php
        $servername = "localhost";$username = "root";$password = "";$dbname = "chatsystem";$conn = new mysqli($servername, $username, $password, $dbname);if ($conn->connect_error) {die("Not connected to database!");}
        session_start();
        function getRandomString($length) {
            $salt = array_merge(range('a', 'z'), range(0, 9));
            $maxIndex = count($salt) - 1;
            $result = '';
            for ($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, $maxIndex);
            $result .= $salt[$index];
            }
            return $result;
        }
        function encryptthis($data, $key) {
            $encryption_key = base64_decode($key);
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
            return base64_encode($encrypted . '::' . $iv);
        }
        function decryptthis($data, $key) {
            $encryption_key = base64_decode($key);
            list($encrypted_data, $iv) = array_pad(explode('::', base64_decode($data), 2),2,null);
            return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
        }
    ?>  
    <style>
    * {
        font-family: Verdana, Geneva, Tahoma, sans-serif;
        box-sizing: border-box;
    }
        input, label, select, textarea {
            display: block;
            padding: 5px;
            margin: 5px;
            width: 300px;
            resize: none;
        }
        .msgContainer {
            width: 300px;
            height: 500px;
            overflow-y: scroll;
            overflow-x: hidden;
            overflow-wrap: break-word;
        }
    </style>
        <script src="http://code.jquery.com/jquery-latest.js"></script>
        <script type="text/javascript">
            
            setInterval("refreshmsg();",1000); 
            function refreshmsg(){$('#refresh').load(location.href + ' #msgLoader');}
            
        </script>
</head>
<body>
    <?php
        echo '<h3><u>Chat by Geny</u></h3>';
        if(isset($_SESSION['login'])) {
            $usrId = $_SESSION['login'];
            $stmt = $conn->prepare("SELECT * FROM users WHERE idUsers LIKE ?");
            $stmt->bind_param("s", $usrId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo 'Welcome <b>'.$row['nameUsers'].'</b>.<br>';
                }
            } else {
                echo 'error, cannot lookup name';
            }
            $stmt->close();
            echo '<a href="?logoff">Logout</a><br><br>';
            if(isset($_GET['logoff'])) {
                session_destroy();
                header('location: index.php');
            } elseif(isset($_GET['cconversation'])) {
                echo '<a href="index.php">&lt; Back</a><br>';
    ?>
                <?php
                    if(!empty($_POST['convName'])) {
                        if(isset($_POST['convSubmit'])) {
                            $convName = htmlspecialchars($_POST['convName']);
                            $convKey = getRandomString(75);
                            $stmt = $conn->prepare("INSERT INTO conversation (titleConversation, keyConversation, owner_fsConversation) VALUES (?, ?, ?);");
                            $stmt->bind_param("sss", $convName, $convKey, $_SESSION['login']);
                            $stmt->execute();
                            $stmt->close();
                            sleep(1);
                            $usrId = $_SESSION['login'];
                            $stmt1 = $conn->prepare("SELECT * FROM users, conversation WHERE owner_fsConversation LIKE idUsers AND owner_fsConversation LIKE ? ORDER BY idConversation DESC LIMIT 0, 1 ");
                            $stmt1->bind_param("s", $usrId);
                            $stmt1->execute();
                            $result = $stmt1->get_result();
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $stmt = $conn->prepare("INSERT INTO usertoconversation(user_fsUserToConversation, conversation_fsUserToConversation) VALUES (?, ?);");
                                    $stmt->bind_param("ss", $_SESSION['login'], $row['idConversation']);
                                    $stmt->execute();
                                    echo 'Conversation: <b>'.$row['titleConversation'].'</b> successfully Created. <a href="?conv='.$row['idConversation'].'">Join</a>!';
                                    $stmt->close();
                                }
                            } else {
                                echo 'error';
                            }
                            $stmt1->close();
                        }
                    }
                ?>
                <form method="post">
                    <label>Create conversation</label>
                    <input type="text" name="convName" placeholder="Title">
                    <input type="submit" name="convSubmit" value="Create">
                </form>
    <?php
            } elseif(isset($_GET['conv'])) {
                echo '<a href="index.php">&lt; Back</a><br>';
                $convId = htmlspecialchars($_GET['conv']);
                $stmt1 = $conn->prepare("SELECT * FROM conversation, usertoconversation WHERE conversation_fsUserToConversation LIKE idConversation AND user_fsUserToConversation LIKE ? AND idConversation LIKE ?");
                $stmt1->bind_param("ss",$_SESSION['login'] , $convId);
                $stmt1->execute();
                $result = $stmt1->get_result();
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<h3>Chat: <b>'.$row['titleConversation'].'</b></h3>';
                        if(isset($_GET['usrmngt'])) {
                            $stmt6 = $conn->prepare("SELECT * FROM users, usertoconversation, conversation WHERE conversation_fsUserToConversation LIKE idConversation AND user_fsUserToConversation LIKE idUsers AND conversation_fsUserToConversation LIKE ?");
                            $convId = htmlspecialchars($_GET['conv']);
                            $stmt6->bind_param("s", $convId);
                            $stmt6->execute();
                            $result6 = $stmt6->get_result();
                            echo '<h4>User:</h4>';
                            if ($result6->num_rows > 0) {
                                echo '<i><b>'.$result6->num_rows.'</b> User</i><br>';
                                while($row6 = $result6->fetch_assoc()) {
                                    if($row6['owner_fsConversation'] == $row6['idUsers']) {
                                        echo '- <b>'.$row6['nameUsers'].'</b><br>';
                                    } else {
                                        echo '- '.$row6['nameUsers'].'<br>';
                                    }
                                }
                            } else {
                                echo 'No User';
                            }
                            $stmt6->close();
                            if($row['owner_fsConversation'] == $_SESSION['login']) {
                                if(isset($_GET['added'])) {
                                    echo '<br>Added User successfully.';
                                } elseif(isset($_GET['rem'])) {
                                    echo '<br>Removed User successfully';
                                } 
                                if(isset($_POST['addusrBtn'])) {
                                    $urnameSetup = $_POST['usrname2'];
                                    $confnameI = htmlspecialchars($_GET['conv']);
                                    $stmt = $conn->prepare("INSERT INTO usertoconversation(user_fsUserToConversation, conversation_fsUserToConversation) VALUES (?, ?);");
                                    $stmt->bind_param("ss", $urnameSetup, $confnameI);
                                    $stmt->execute();
                                    header('location: ?conv='.$_GET['conv'].'&usrmngt&added');
                                    $stmt->close();
                                }
                                echo '<h3>Add User</h3>';
                                echo '<form method="post">';
                                echo '<select name="usrname2">';
                                $stmt = $conn->prepare("SELECT * FROM users WHERE idUsers NOT IN (SELECT user_fsUserToConversation FROM users, usertoconversation WHERE user_fsUserToConversation LIKE idUsers AND conversation_fsUserToConversation LIKE ?)");
                                $convId = htmlspecialchars($_GET['conv']);
                                $stmt->bind_param("s", $convId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo '<option value="'.$row['idUsers'].'">'.$row['nameUsers'].'</option>';
                                    }
                                } else {
                                    echo '<option value"null">No Users</option>';;
                                }
                                $stmt->close();
                                echo '</select>';
                                echo '<input type="submit" name="addusrBtn" value="Add">';
                                echo '</form>';
                                
                                if(isset($_POST['remusrBtn'])) {
                                    $urnameSetup = $_POST['usrname1'];
                                    $confnameI = htmlspecialchars($_GET['conv']);
                                    $stmt = $conn->prepare("DELETE FROM usertoconversation WHERE user_fsUserToConversation LIKE ? AND conversation_fsUserToConversation LIKE ? AND user_fsUserToConversation NOT IN (SELECT owner_fsConversation FROM conversation WHERE idConversation LIKE ?)");
                                    $stmt->bind_param("sss", $urnameSetup, $confnameI, $confnameI);
                                    $stmt->execute();
                                    header('location: ?conv='.$_GET['conv'].'&usrmngt&rem');
                                    $stmt->close();
                                }
                                echo '<h3>Remove User</h3>';
                                echo '<form method="post">';
                                echo '<select name="usrname1">';
                                $stmt = $conn->prepare("SELECT * FROM users, usertoconversation WHERE user_fsUserToConversation LIKE idUsers AND conversation_fsUserToConversation LIKE ?");
                                $convId = htmlspecialchars($_GET['conv']);
                                $stmt->bind_param("s", $convId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        if($row['idUsers'] != $_SESSION['login']) {
                                            echo '<option value="'.$row['idUsers'].'">'.$row['nameUsers'].'</option>';
                                        }
                                    }
                                } else {
                                    echo '<option value"null">No Users</option>';
                                }
                                $stmt->close();
                                echo '</select>';
                                echo '<input type="submit" name="remusrBtn" value="remove">';
                                echo '</form>';
                            }
                        } elseif(isset($_GET['escape'])) {
                            if(isset($_GET['yes'])) {
                                $stmt = $conn->prepare("SELECT owner_fsConversation FROM conversation WHERE idConversation LIKE ?");
                                $convId = htmlspecialchars($_GET['conv']);
                                $stmt->bind_param("s", $convId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        if($row['owner_fsConversation'] == $_SESSION['login']) {
                                            echo 'You are The Owner of this chat, you cant leave! <a href="?conv='.$_GET['conv'].'">Back</a> or <a href="?conv='.$_GET['conv'].'&amp;delete">delete</a>';
                                        } else {
                                            $urnameSetup = $_SESSION['login'];
                                            $confnameI = htmlspecialchars($_GET['conv']);
                                            $stmt1 = $conn->prepare("DELETE FROM usertoconversation WHERE user_fsUserToConversation LIKE ? AND conversation_fsUserToConversation LIKE ?");
                                            $stmt1->bind_param("ss", $urnameSetup, $confnameI);
                                            $stmt1->execute();
                                            header('location: index.php');
                                            $stmt1->close();
                                        }
                                    }
                                } else {
                                    echo '<option value"null">No Users</option>';;
                                }
                            } else {
                                echo '<h3>Leave chat?</h3>';
                                echo '<a href="?conv='.$_GET['conv'].'&amp;escape&amp;yes"><button>Yes</button></a>';
                                echo '<a href="?conv='.$_GET['conv'].'"><button>No</button></a>';
                            }
                        } elseif(isset($_GET['delete'])) {
                            if($row['owner_fsConversation'] == $_SESSION['login']) {
                                if(isset($_GET['yes'])) {
                                    $confnameI = htmlspecialchars($_GET['conv']);
                                    $stmt22 = $conn->prepare("DELETE FROM message WHERE conversation_fsMessage LIKE ?");
                                    $stmt22->bind_param("s", $confnameI);
                                    $stmt22->execute();
                                    $stmt22->close();
                                    $stmt22 = $conn->prepare("DELETE FROM usertoconversation WHERE conversation_fsUserToConversation LIKE ?");
                                    $stmt22->bind_param("s", $confnameI);
                                    $stmt22->execute();
                                    $stmt22->close();
                                    $stmt22 = $conn->prepare("DELETE FROM conversation WHERE idConversation LIKE ?");
                                    $stmt22->bind_param("s", $confnameI);
                                    $stmt22->execute();
                                    $stmt22->close();
                                    header('location: index.php');
                                } else {
                                    echo '<h3>Remove chat?</h3>';
                                    echo '<a href="?conv='.$_GET['conv'].'&amp;delete&amp;yes"><button>Yes</button></a>';
                                    echo '<a href="?conv='.$_GET['conv'].'"><button>No</button></a>';
                                }
                            } else {
                                echo 'You dont habve the permission to Delete this chat. <a href="?conv='.$_GET['conv'].'">Back</a>';
                            }
                        } elseif(isset($_GET['delmsg'])) {
                            $userSent = $_SESSION['login'];
                            $msgID = htmlspecialchars($_GET['delmsg']);
                            $stmt = $conn->prepare("SELECT * FROM message WHERE idMessage LIKE ? AND user_fsMessage LIKE ?");
                            $stmt->bind_param("ss",$msgID, $userSent);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    if(isset($_GET['yes'])) {
                                        $confnameI = htmlspecialchars($_GET['conv']);
                                        $stmt22 = $conn->prepare("DELETE FROM message WHERE idMessage LIKE ? AND conversation_fsMessage LIKE ?");
                                        $stmt22->bind_param("ss", $msgID, $confnameI);
                                        $stmt22->execute();
                                        $stmt22->close();
                                        header('location: ?conv='.$_GET['conv']);
                                    } else {
                                        echo '<h3>Remove Message?</h3>';
                                        echo '<a href="?conv='.$_GET['conv'].'&amp;delmsg='.$_GET['delmsg'].'&amp;yes"><button>Yes</button></a>';
                                        echo '<a href="?conv='.$_GET['conv'].'"><button>No</button></a>';
                                    }
                                }
                            } else {
                                echo 'Message not found.';
                            }
                        } else {
                            if($row['owner_fsConversation'] == $_SESSION['login']) {
                                echo '<a href="?conv='.$_GET['conv'].'&amp;delete">Delete chat &gt;</a><br>';
                            }
                            echo '<a href="?conv='.$_GET['conv'].'&amp;escape">Escape this chat &gt;</a><br>';
                            echo '<a href="?conv='.$_GET['conv'].'&amp;usrmngt">User Management &gt;</a><br><br>';
                            echo '<div id="refresh" class="msgContainer">';
                            $stmt = $conn->prepare("SELECT * FROM message, users WHERE user_fsMessage LIKE idUsers AND conversation_fsMessage LIKE ? ORDER BY dateSentMessage DESC LIMIT 0, 30");
                            $stmt->bind_param("s", $convId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                $stmt1 = $conn->prepare("SELECT * FROM Conversation WHERE idConversation LIKE ?");
                                $confnameI = htmlspecialchars($_GET['conv']);
                                $stmt1->bind_param("s", $confnameI);
                                $stmt1->execute();
                                $result1 = $stmt1->get_result();
                                if ($result1->num_rows > 0) {
                                    while($row1 = $result1->fetch_assoc()) {
                                       $key = $row1['keyConversation'];
                                    }
                                } else {
                                    echo 'No key found';
                                }
                                    echo '<div id="msgLoader">';
                                    $msgDisplay = decryptthis($row['messageMessage'], $key);
                                    $datesend = date("d.m.y H:i:s", strtotime($row['dateSentMessage'])).'uhr';
                                    if($row['idUsers'] == $_SESSION['login']) {
                                        echo $datesend.'<b> <font size="1"><a href="?conv='.$_GET['conv'].'&amp;delmsg='.$row['idMessage'].'">Delete</a></font><br><i><font color="black">You</font></i></b>:<br>';
                                    } else {
                                        echo $datesend.'<b><br><u><font color="red">'.$row['nameUsers'].'</font></u></b>:<br>';
                                    }
                                    echo '&nbsp;'.$msgDisplay.'<br></br>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div id="msgLoader">No message found!</div>';
                            }
                            echo '<div id="loaderfunc"></div>';
                            echo '</div>';
                            if(isset($_POST['submitchat'])) {
                                if(!empty($_POST['chatmsg'])) {
                                    $stmt = $conn->prepare("SELECT * FROM Conversation WHERE idConversation LIKE ?");
                                    $confnameI = htmlspecialchars($_GET['conv']);
                                    $stmt->bind_param("s", $confnameI);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $msg = htmlspecialchars($_POST['chatmsg']);
                                            $encrypted = encryptthis($msg, $row['keyConversation']);
                                            $urnameSetup = $_SESSION['login'];
                                            $confnameI = htmlspecialchars($_GET['conv']);
                                            $stmt5 = $conn->prepare("INSERT INTO message(messageMessage, user_fsMessage, conversation_fsMessage) VALUES (?, ?, ?);");
                                            $stmt5->bind_param("sss", $encrypted, $urnameSetup, $confnameI);
                                            $stmt5->execute();

                                            $stmt5->close();
                                        }
                                    } else {
                                        echo 'No key found';
                                    }
                                }
                            }
                            echo '<form method="post">';
                            echo '<textarea autofocus id="textBoxChat" name="chatmsg"></textarea>';
                            echo '<input type="submit" name="submitchat" value="send">';
                            echo '</form>';
                            $stmt->close();
                        }
                    }
                } else {
                    echo 'No conversation or no Permission!';
                }
                $stmt1->close();
            } else {
                echo '<a href="?cconversation">Create conversation &gt;</a><br>';
                $usrId = $_SESSION['login'];
                $stmt = $conn->prepare("SELECT * FROM users, conversation, usertoconversation WHERE user_fsUserToConversation LIKE idUsers AND idConversation LIKE conversation_fsUserToConversation AND idusers LIKE ?");
                $stmt->bind_param("s", $usrId);
                $stmt->execute();
                $result = $stmt->get_result();
                echo '<h3>Conversations: </h3>';
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<a href="?conv='.$row['idConversation'].'"><div>'.$row['titleConversation'].'</div></a>';
                    }
                } else {
                    echo 'No Conversations';
                }
                $stmt->close();
            }
        } else {
    ?>
            <?php
                if(isset($_POST['submit'])) {
                    if(!empty($_POST['name'])) {
                        $usrLogin = htmlspecialchars($_POST['name']);
                        $stmt = $conn->prepare("SELECT * FROM users WHERE nameUsers LIKE ?");
                        $stmt->bind_param("s", $usrLogin);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $_SESSION['login'] = $row['idUsers'];
                            header('location: index.php');
                            }
                        } else {
                            echo 'Username not registred';
                        }
                        $stmt->close();
                    }
                }
            ?>
            <form method="post">
                <label>Login</label>
                <input type="text" name="name" placeholder="Username">
                <input type="submit" name="submit" value="login">
            </form>
    <?php
        }
        $conn->close();
    ?>
</body>
</html>