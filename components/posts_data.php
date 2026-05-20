<?php
/**
 * SMFE Posts Data
 * Tries to load posts from the database (via db/config.php).
 * Falls back to dummy/demo posts if the database is not yet configured.
 */

function getPosts(string $tag = '', int $page = 1, int $limit = 20): array {
  // Try DB first
  try {
    if (file_exists(__DIR__ . '/../db/config.php')) {
      require_once __DIR__ . '/../db/config.php';
      $db = tryGetDB();
      if (!$db) return getDummyPosts($tag);
      $offset = ($page - 1) * $limit;
      $userId = $_SESSION['user']['id'] ?? 0;

      $where  = ($tag && $tag !== 'All') ? 'WHERE p.tag = ?' : '';
      $params = ($tag && $tag !== 'All') ? [$tag] : [];

      $likedSub = $userId
        ? "(SELECT COUNT(*) FROM likes lme WHERE lme.post_id = p.id AND lme.user_id = {$userId}) AS user_liked"
        : "0 AS user_liked";

      $sql = "
        SELECT
          p.id, p.body, p.math, p.tag,
          p.image_path, p.created_at,
          u.username, u.avatar_letter,
          (SELECT COUNT(*) FROM likes   l WHERE l.post_id = p.id) AS likes,
          (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
          {$likedSub}
        FROM posts p
        JOIN users u ON u.id = p.user_id
        {$where}
        ORDER BY likes DESC, p.created_at DESC
        LIMIT {$limit} OFFSET {$offset}
      ";
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $posts = $stmt->fetchAll();

      $postIds  = array_column($posts, 'id');
      $comments = [];
      if ($postIds) {
        $in    = implode(',', array_map('intval', $postIds));
        $cstmt = $db->query("
          SELECT c.id, c.post_id, c.text, c.math, u.username, u.avatar_letter
          FROM comments c JOIN users u ON u.id = c.user_id
          WHERE c.post_id IN ({$in})
          ORDER BY c.created_at ASC
        ");
        foreach ($cstmt->fetchAll() as $row) {
          $comments[$row['post_id']][] = $row;
        }
      }

      foreach ($posts as &$p) {
        $all = $comments[$p['id']] ?? [];
        $p['comments_data']  = array_slice($all, 0, 3);
        $p['comments_total'] = (int)$p['comment_count'];
        $p['user_liked']    = (bool)$p['user_liked'];
        $diff = time() - strtotime($p['created_at']);
        if      ($diff < 60)    $p['time'] = 'just now';
        elseif  ($diff < 3600)  $p['time'] = floor($diff/60) . ' min ago';
        elseif  ($diff < 86400) $p['time'] = floor($diff/3600) . ' hr ago';
        else                    $p['time'] = floor($diff/86400) . ' days ago';
      }
      unset($p);

      return $posts;
    }
  } catch (Exception $e) {
    // DB not configured or error — fall through to dummy data
  }

  // ── Fallback: demo/dummy posts ─────────────────────────────────────────────
  return getDummyPosts($tag);
}

/** @deprecated Use getPosts() instead */
function getDummyPosts(string $filterTag = ''): array {
  $all = [
    ['id'=>1,'username'=>'Kavindra_LK','avatar_letter'=>'K','time'=>'2 min ago','tag'=>'Calculus',
     'body'=>'How do I evaluate this definite integral?',
     'math'=>'∫[0 to π] sin(x) dx','likes'=>48,'comment_count'=>12,
     'comments_data'=>[
       ['username'=>'Amara_M','text'=>'Use the antiderivative of sin(x), which is -cos(x). Then evaluate from 0 to π.'],
       ['username'=>'RajMath','text'=>'The answer is 2. [-cos(π)] - [-cos(0)] = 1 + 1 = 2'],
     ],'user_liked'=>false],
    ['id'=>2,'username'=>'Priya_S','avatar_letter'=>'P','time'=>'8 min ago','tag'=>'Algebra',
     'body'=>'Can someone explain how to solve a quadratic using completing the square method?',
     'math'=>'x² + 6x + 5 = 0','likes'=>35,'comment_count'=>8,
     'comments_data'=>[
       ['username'=>'Kavindra_LK','text'=>'Move the constant: x² + 6x = -5, then add (6/2)² = 9 to both sides.'],
     ],'user_liked'=>false],
    ['id'=>3,'username'=>'RajMath','avatar_letter'=>'R','time'=>'15 min ago','tag'=>'Statistics',
     'body'=>'What is the difference between population standard deviation and sample standard deviation?',
     'math'=>'σ = √(Σ(xᵢ − μ)²/N)  vs  s = √(Σ(xᵢ − x̄)²/(n−1))','likes'=>62,'comment_count'=>19,
     'comments_data'=>[
       ['username'=>'Amara_M','text'=>'σ is population SD (divide by N). s is sample SD (divide by n-1, Bessel\'s correction).'],
     ],'user_liked'=>false],
    ['id'=>4,'username'=>'Amara_M','avatar_letter'=>'A','time'=>'22 min ago','tag'=>'Trigonometry',
     'body'=>'How to prove this identity? I keep getting stuck at the second step.',
     'math'=>'sin²θ + cos²θ = 1','likes'=>29,'comment_count'=>5,
     'comments_data'=>[],'user_liked'=>false],
    ['id'=>5,'username'=>'Sena_2024','avatar_letter'=>'S','time'=>'34 min ago','tag'=>'Linear Algebra',
     'body'=>'Can anyone help me find the eigenvalues of this matrix?',
     'math'=>'A = [[3, 1], [0, 2]] → det(A − λI) = 0','likes'=>41,'comment_count'=>9,
     'comments_data'=>[
       ['username'=>'RajMath','text'=>'The eigenvalues are λ = 3 and λ = 2 (upper triangular — diagonal entries are eigenvalues).'],
     ],'user_liked'=>false],
    ['id'=>6,'username'=>'NimaliK','avatar_letter'=>'N','time'=>'47 min ago','tag'=>'Differential Equations',
     'body'=>'Struggling with this ODE. What method should I use?',
     'math'=>'dy/dx + 2y = e^(−x)','likes'=>55,'comment_count'=>14,
     'comments_data'=>[
       ['username'=>'Priya_S','text'=>'This is a first-order linear ODE. Use the integrating factor method: μ(x) = e^∫2dx = e^(2x).'],
     ],'user_liked'=>false],
    ['id'=>7,'username'=>'TharinduW','avatar_letter'=>'T','time'=>'1 hr ago','tag'=>'Number Theory',
     'body'=>'Is there an elegant proof that there are infinitely many primes?',
     'math'=>"Euclid: Assume finite primes p₁...pₙ, then N = (p₁×...×pₙ)+1 is not divisible by any pᵢ",
     'likes'=>88,'comment_count'=>22,
     'comments_data'=>[
       ['username'=>'Amara_M','text'=>'Euclid\'s proof is the classic. Also check Euler\'s proof using the divergence of the sum of reciprocals of primes.'],
     ],'user_liked'=>false],
    ['id'=>8,'username'=>'FaridaH','avatar_letter'=>'F','time'=>'1.5 hr ago','tag'=>'Complex Analysis',
     'body'=>"What exactly does Euler's formula tell us geometrically?",
     'math'=>'e^(iθ) = cos θ + i·sin θ','likes'=>76,'comment_count'=>18,
     'comments_data'=>[],'user_liked'=>false],
    ['id'=>9,'username'=>'Kavindra_LK','avatar_letter'=>'K','time'=>'2 hr ago','tag'=>'Probability',
     'body'=>"Bayes theorem: a test is 99% accurate and disease affects 0.1% of population. If you test positive, what's the probability?",
     'math'=>'P(D|+) = P(+|D)P(D) / P(+)','likes'=>94,'comment_count'=>31,
     'comments_data'=>[
       ['username'=>'TharinduW','text'=>'This is the classic Bayes\' theorem example! The answer is about 9%, much lower than people expect.'],
       ['username'=>'NimaliK','text'=>'P(D|+) = (0.99 × 0.001) / (0.99×0.001 + 0.01×0.999) ≈ 0.0902 = 9%'],
     ],'user_liked'=>false],
    ['id'=>10,'username'=>'Priya_S','avatar_letter'=>'P','time'=>'2.5 hr ago','tag'=>'Calculus',
     'body'=>'What is the formal epsilon-delta definition of a limit?',
     'math'=>'lim(x→a) f(x) = L  ⟺  ∀ε>0, ∃δ>0 : 0<|x−a|<δ → |f(x)−L|<ε',
     'likes'=>67,'comment_count'=>16,'comments_data'=>[],'user_liked'=>false],
    ['id'=>11,'username'=>'Sena_2024','avatar_letter'=>'S','time'=>'3 hr ago','tag'=>'Geometry',
     'body'=>'How do you derive the formula for the area of a circle?',
     'math'=>'A = πr²  (derived via integration: ∫[0 to r] 2πx dx)',
     'likes'=>53,'comment_count'=>11,'comments_data'=>[],'user_liked'=>false],
    ['id'=>12,'username'=>'RajMath','avatar_letter'=>'R','time'=>'3.5 hr ago','tag'=>'Discrete Math',
     'body'=>'Can someone explain strong induction vs weak induction?',
     'math'=>'P(1) ∧ [∀k≤n, P(k)] → P(n+1)  ⊢  ∀n P(n)',
     'likes'=>44,'comment_count'=>7,'comments_data'=>[],'user_liked'=>false],
    ['id'=>13,'username'=>'Amara_M','avatar_letter'=>'A','time'=>'4 hr ago','tag'=>'Linear Algebra',
     'body'=>'What is the geometric meaning of the dot product vs cross product?',
     'math'=>'a·b = |a||b|cosθ   |a×b| = |a||b|sinθ',
     'likes'=>71,'comment_count'=>20,'comments_data'=>[],'user_liked'=>false],
    ['id'=>14,'username'=>'FaridaH','avatar_letter'=>'F','time'=>'5 hr ago','tag'=>'Calculus',
     'body'=>'What is the intuition behind the Fourier transform?',
     'math'=>'F(ω) = ∫[-∞ to ∞] f(t)·e^(−iωt) dt',
     'likes'=>83,'comment_count'=>25,'comments_data'=>[],'user_liked'=>false],
    ['id'=>15,'username'=>'TharinduW','avatar_letter'=>'T','time'=>'5.5 hr ago','tag'=>'Probability',
     'body'=>'How many ways can you arrange the letters in MISSISSIPPI?',
     'math'=>'11! / (4! × 4! × 2! × 1!) = 34,650',
     'likes'=>39,'comment_count'=>6,'comments_data'=>[],'user_liked'=>false],
    ['id'=>16,'username'=>'NimaliK','avatar_letter'=>'N','time'=>'6 hr ago','tag'=>'Real Analysis',
     'body'=>'What makes a function Riemann integrable vs Lebesgue integrable?',
     'math'=>'Riemann: partition domain  |  Lebesgue: partition range',
     'likes'=>92,'comment_count'=>28,'comments_data'=>[],'user_liked'=>false],
    ['id'=>17,'username'=>'Kavindra_LK','avatar_letter'=>'K','time'=>'7 hr ago','tag'=>'Abstract Algebra',
     'body'=>'What is a group and can someone give a simple real-world example?',
     'math'=>'(G,·): closure, associativity, identity e, inverse a⁻¹',
     'likes'=>58,'comment_count'=>13,'comments_data'=>[],'user_liked'=>false],
    ['id'=>18,'username'=>'Sena_2024','avatar_letter'=>'S','time'=>'8 hr ago','tag'=>'Calculus',
     'body'=>"How does Newton's method for root finding work and when does it fail?",
     'math'=>'xₙ₊₁ = xₙ − f(xₙ)/f\'(xₙ)',
     'likes'=>47,'comment_count'=>9,'comments_data'=>[],'user_liked'=>false],
    ['id'=>19,'username'=>'RajMath','avatar_letter'=>'R','time'=>'9 hr ago','tag'=>'Discrete Math',
     'body'=>'What is the difference between a tree and a spanning tree?',
     'math'=>'Spanning tree of G: connected subgraph with n−1 edges, no cycles',
     'likes'=>36,'comment_count'=>8,'comments_data'=>[],'user_liked'=>false],
    ['id'=>20,'username'=>'Priya_S','avatar_letter'=>'P','time'=>'10 hr ago','tag'=>'Topology',
     'body'=>'Can someone explain what a homeomorphism is in simple terms?',
     'math'=>'f: X→Y homeomorphism ⟺ f bijective, f & f⁻¹ continuous',
     'likes'=>101,'comment_count'=>34,'comments_data'=>[],'user_liked'=>false],
  ];

  if ($filterTag && $filterTag !== 'All') {
    $all = array_values(array_filter($all, fn($p) => $p['tag'] === $filterTag));
  }
  usort($all, fn($a,$b) => $b['likes'] - $a['likes']);
  return $all;
}
?>
