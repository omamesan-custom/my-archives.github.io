// 使用モジュール
const Engine = Matter.Engine,
    Render = Matter.Render,
    Runner = Matter.Runner,
    Body = Matter.Body,
    Bodies = Matter.Bodies,
    Composite = Matter.Composite,
    Composites = Matter.Composites,
    Vector = Matter.Vector,
    Constraint = Matter.Constraint,
    MouseConstraint = Matter.MouseConstraint,
    Mouse = Matter.Mouse,
    Events = Matter.Events;

// エンジンの生成
const engine = Engine.create();

// 物理演算canvasを挿入する要素
const canvas = $('#canvas-area')[0];

// レンダリングの設定
const render = Render.create({
    element: canvas,
    engine: engine,
    options: {
        width: 800,
        height: 600,
    }
});

// マウス、マウス制約を生成
const mouse = Mouse.create(canvas);
const mouseConstraint = MouseConstraint.create(engine, {
    mouse: mouse,
    constraint: {
        render: {
        visible: false
        }
    }
})

Composite.add(engine.world, mouseConstraint)
render.mouse = mouse

// レンダリングを実行
Render.run(render);

// エンジンを実行
Runner.run(engine);

engine.world.gravity.y = 0.3;


let count = 0;
let timerID = setInterval('countup()',1000);

function countup(){
  count++;
}

function gameOver(){
  clearInterval(timerID);
  const countDisplay = document.getElementById('count-display');
  countDisplay.textContent = count;
  const gameOverAlert = document.getElementById('game-over-alert');
  gameOverAlert.style.display = 'block';
}

// 壁の設定
const rightWall = Bodies.rectangle(20, 300, 20, 580, { isStatic: true });
const leftWall = Bodies.rectangle(780, 300, 20, 580, { isStatic: true });
const topWall = Bodies.rectangle(400, 20, 780, 20, { isStatic: true });

Composite.add(engine.world, [rightWall, leftWall, topWall]);


const pit = Bodies.rectangle(400, 900, 50000, 30, { isStatic: true, label: 'pit' });
Composite.add(engine.world, [pit]);


// 静止オブジェクト（右上）
const rightObject = Bodies.polygon(600, 180, 5, 80, { isStatic: true });
// 静止オブジェクト（左上）
const leftObject = Bodies.polygon(170, 170, 7, 60, { isStatic: true });

// 拘束されない円（手球）
const Circle = Bodies.circle(200, 100, 30);

// 拘束される物体（中央の板）
const boundSquare = Bodies.rectangle(400, 450, 200, 30, {restitution: 2});

Composite.add(engine.world, [rightObject, leftObject, Circle, boundSquare]);


// 板と2本のバネ接続（板の両端と空間点の接続）
const constraint2Left = Constraint.create({
  pointA: { x: 50, y: 500 },
  bodyB: boundSquare,
  pointB: { x: -100, y: 0 },
  stiffness: 0.1
});
const constraint2Right = Constraint.create({
  pointA: { x: 750, y: 500 },
  bodyB: boundSquare,
  pointB: { x: 100, y: 0 },
  stiffness: 0.1
});
Composite.add(engine.world, [constraint2Left, constraint2Right]);


Events.on(engine, 'collisionStart', e => {
  $.each(e.pairs, (i, pair) => {
    // 画面外落下判定オブジェクトに衝突したボールを削除する
    if (pair.bodyA.label === 'pit') {
        gameOver();
    }
  })
});

